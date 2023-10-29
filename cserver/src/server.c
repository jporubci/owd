/* server.c
 * Jozef Porubcin */

/* Server libraries */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netdb.h>
#include <errno.h>
#include <unistd.h>
#include <signal.h>

/* MySQL libraries */
#include <mysql/mysql.h>

/* Header files */
#include "../include/server_func.h"

/* Global */
unsigned char SERVE = 1;

void handler(int signo, siginfo_t *info, void *context) {
    SERVE = 0;
}

/* Main */
int main(int argc, char *argv[]) {
    
    /* Variables */
    char *port = NULL;
    
    /* Parse command line arguments */
    for (int i = 1; i < argc && i > 0; i++) {
        
        /* --help */
        if (!strcmp(argv[i], "--help")) {
            usage(EXIT_SUCCESS);
            
        /* PORT */
        } else if (!port) {
            port = argv[i];
            
        } else {
            usage(EXIT_FAILURE);
        }
    }
    
    if (!port) {
        usage(EXIT_FAILURE);
    }
    
    /* Listen on port */
    int server_socket_fd = socket_listen(port);
    if (server_socket_fd == -1) {
        return EXIT_FAILURE;
    }
    
    /* gethostname */
    char hostname[BUFSIZ];
    if (gethostname(hostname, sizeof(hostname)) == -1) {
        fprintf(stderr, "gethostname failed: %s", strerror(errno));
        return EXIT_FAILURE;
    }
    
    /* MySQL Initialization */
    MYSQL *mysql = mysql_init(NULL);
    if (!mysql) {
        fprintf(stderr, "mysql_init failed: %s", mysql_error(mysql));
        return EXIT_FAILURE;
    }
    
    if (!mysql_real_connect(mysql, "localhost", "jporubci", "goirish", "jporubci", atoi(port), NULL, 0)) {
        fprintf(stderr, "mysql_real_connect failed: %s", mysql_error(mysql));
        return EXIT_FAILURE;
    }
    
    printf("Serving on %s:%s\n", hostname, port);
    
    /* Set up CTRL+C handler */
    struct sigaction act = {.sa_sigaction = handler};
    if (sigaction(SIGINT, &act, NULL) == -1) {
        fprintf(stderr, "sigaction failed: %s", strerror(errno));
        return EXIT_FAILURE;
    }
    
    /* Serve */
    while (SERVE) {
        
        /* Accept a client */
        FILE *client_fp = accept_client(server_socket_fd);
        if (!client_fp) {
            continue;
        }
        
        /* Handle client request */
        handle_request(client_fp, mysql);
        fclose(client_fp);
    }
    
    mysql_close(mysql);
    
    return EXIT_SUCCESS;
}
