#ifndef SERVER_H
#define SERVER_H

#include <stdio.h>
#include <stdlib.h>
#include <sys/stat.h>
#include <string.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netdb.h>
#include <errno.h>

#define TEMPLATES_DIR "templates"

void usage(int status) {
    fprintf(stderr, "Usage: ./server [OPTION] PORT\n");
    fprintf(stderr, "Host a server on port PORT.\n\n");
    fprintf(stderr, "Options:\n");
    fprintf(stderr, "    --help   display this help and exit\n");
    exit(status);
}

/* Get server socket file descriptor from port */
int socket_listen(const char* port) {
    
    /* getaddrinfo */
    struct addrinfo hints = {
        .ai_family      = AF_UNSPEC,    /* Use either IPv4 or IPv6 */
        .ai_socktype    = SOCK_STREAM,  /* Use TCP */
        .ai_protocol    = 0,            /* Use socket addresses with any protocol */
        .ai_flags       = AI_PASSIVE,   /* Use all interfaces to listen */
    };
    struct addrinfo* results;
    int gai_status;
    if ((gai_status = getaddrinfo(NULL, port, &hints, &results)) != 0) {
        fprintf(stderr, "getaddrinfo failed: %s\n", gai_strerror(gai_status));
        return EXIT_FAILURE;
    }
    
    /* For each address entry, allocate socket, bind, and listen */
    int server_socket_fd = -1;
    for (struct addrinfo* p = results; p && server_socket_fd == -1; p = p->ai_next) {
        /* socket */
        if ((server_socket_fd = socket(p->ai_family, p->ai_socktype, p->ai_protocol)) == -1) {
            fprintf(stderr, "socket failed: %s\n", strerror(errno));
            continue;
        }
        
        /* bind */
        if (bind(server_socket_fd, p->ai_addr, p->ai_addrlen) == -1) {
            fprintf(stderr, "bind failed: %s\n", strerror(errno));
            close(server_socket_fd);
            server_socket_fd = -1;
            continue;
        }
        
        /* listen */
        if (listen(server_socket_fd, SOMAXCONN) == -1) {
            fprintf(stderr, "listen failed: %s\n", strerror(errno));
            close(server_socket_fd);
            server_socket_fd = -1;
            continue;
        }
    }
    freeaddrinfo(results);
    
    return server_socket_fd;
}

/* Get FILE* for client */
FILE* accept_client(int server_socket_fd) {
    
    /* accept */
    struct sockaddr client_addr;
    socklen_t client_addr_len;
    int client_socket_fd;
    if ((client_socket_fd = accept(server_socket_fd, &client_addr, &client_addr_len)) == -1) {
        fprintf(stderr, "accept failed: %s\n", strerror(errno));
        return NULL;
    }
    
    /* fdopen */
    FILE* client_fp;
    if (!(client_fp = fdopen(client_socket_fd, "w+"))) {
        fprintf(stderr, "fdopen failed: %s\n", strerror(errno));
        close(client_socket_fd);
        return NULL;
    }
    
    return client_fp;
}

/* Handle client request */
void handle_request(FILE* client_fp) {
    
    char* WHITESPACE = " \t\r\n";
    
    /* Parse URI */
    char request_buffer[BUFSIZ];
    if(!fgets(request_buffer, sizeof(request_buffer), client_fp)) {
        fprintf(stderr, "fgets failed\n");
        return;
    }
    
    fprintf(stderr, "%s", request_buffer);
    
    char* method = strtok(request_buffer, WHITESPACE);
    char* path = strtok(NULL, WHITESPACE);
    char* http_version = strtok(NULL, WHITESPACE);
    
    /* Skip headers */
    char header_buffer[BUFSIZ];
    while (fgets(header_buffer, sizeof(header_buffer), client_fp) && strlen(header_buffer) > 2);
    
    /* Return message */
    if (!strcmp(method, "GET")) {
        
        char template_buffer[BUFSIZ];
        strncpy(template_buffer, TEMPLATES_DIR, sizeof(TEMPLATES_DIR)+1);
        strncat(template_buffer, path, BUFSIZ - sizeof(TEMPLATES_DIR)-1);
        strncat(template_buffer, ".html", BUFSIZ - sizeof(TEMPLATES_DIR)-1 - 5);
        
        /* stat */
        struct stat sb;
        if (stat(template_buffer, &sb) == -1) {
            fprintf(stderr, "stat failed: %s\n", strerror(errno));
            fprintf(client_fp, "%s 404 Not Found\r\n", http_version);
            fprintf(client_fp, "\r\n");
            return;
        }
        
        /* open */
        int fd = open(template_buffer, O_RDONLY);
        if (fd == -1) {
            fprintf(stderr, "open failed: %s\n", strerror(errno));
            fprintf(client_fp, "%s 500 Internal Server Error\r\n", http_version);
            fprintf(client_fp, "\r\n");
            return;
        }
        
        /* malloc */
        void* data = malloc(sb.st_size);
        if (data == NULL) {
            fprintf(stderr, "malloc failed\n");
            fprintf(client_fp, "%s 500 Internal Server Error\r\n", http_version);
            fprintf(client_fp, "\r\n");
            close(fd);
            return;
        }
        
        /* read */
        ssize_t num_bytes;
        if ((num_bytes = read(fd, data, sb.st_size)) == -1) {
            fprintf(stderr, "read failed: %s\n", strerror(errno));
            fprintf(client_fp, "%s 500 Internal Server Error\r\n", http_version);
            fprintf(client_fp, "\r\n");
            free(data);
            close(fd);
            return;
        }
        
        /* Respond to client */
        fprintf(client_fp, "%s 200 OK\r\n", http_version);
        fprintf(client_fp, "Content-Type: text/html\n");
        fprintf(client_fp, "Content-Length: %ld\r\n", sb.st_size);
        fprintf(client_fp, "\r\n");
        fwrite(data, 1, sb.st_size, client_fp);
        
        free(data);
        close(fd);
        return;
    }
}
        
#endif
