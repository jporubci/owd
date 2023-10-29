#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/socket.h>
#include <netdb.h>
#include <unistd.h>
#include <errno.h>

#include "../include/client_func.h"

void usage(int status) {
    fprintf(stderr, "Usage: ./client [OPTION] HOST PORT\n");
    fprintf(stderr, "Connect to a server with hostname HOST and port PORT.\n\n");
    fprintf(stderr, "Options:\n");
    fprintf(stderr, "    --help   display this help and exit\n");
    exit(status);
}

FILE * socket_dial(const char *hostname, const char *port) {
    
    /* getaddrinfo */
    struct addrinfo *results;
    struct addrinfo hints = {
        .ai_family      = AF_UNSPEC,    /* Return IPv4 and IPv6 choices */
        .ai_socktype    = SOCK_STREAM,  /* Use TCP */
        .ai_protocol    = 0,
        .ai_flags       = 0,
    };
    
    int gai_status;
    if ((gai_status = getaddrinfo(hostname, port, &hints, &results)) != 0) {
        fprintf(stderr, "getaddrinfo failed: %s\n", gai_strerror(gai_status));
        return NULL;
    }
    
    /* For each server entry, allocate socket and try to connect */
    int server_socket_fd = -1;
    for (struct addrinfo *p = results; p && server_socket_fd == -1; p = p->ai_next) {
        /* socket */
        if ((server_socket_fd = socket(p->ai_family, p->ai_socktype, p->ai_protocol)) == -1) {
            fprintf(stderr, "socket failed: %s\n", strerror(errno));
            continue;
        }
        
        /* connect */
        if (connect(server_socket_fd, p->ai_addr, p->ai_addrlen) == -1) {
            fprintf(stderr, "connect failed: %s\n", strerror(errno));
            close(server_socket_fd);
            server_socket_fd = -1;
            continue;
        }
    }
    freeaddrinfo(results);
    
    if (server_socket_fd == -1) {
        return NULL;
    }
    
    FILE *server_fp = fdopen(server_socket_fd, "w+");
    if (!server_fp) {
        fprintf(stderr, "fdopen failed: %s\n", strerror(errno));
        close(server_socket_fd);
        return NULL;
    }
    
    return server_fp;
}
