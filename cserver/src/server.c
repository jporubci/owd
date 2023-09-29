/* server.c
 * Jozef Porubcin */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netdb.h>
#include <errno.h>
#include <unistd.h>

#include "../include/server_func.h"

int main(int argc, char* argv[]) {
    
    /* Variables */
    char* port = NULL;
    
    /* Parse command line arguments */
    for (int i = 1; i < argc && i > 0; ++i) {
        
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
        return EXIT_FAILURE;
    }
    printf("Serving on %s:%s\n", hostname, port);
    
    /* Serve forever */
    while (1) {
        
        /* Accept a client */
        FILE* client_fp = accept_client(server_socket_fd);
        if (!client_fp) {
            continue;
        }
        
        /* Handle client request */
        handle_request(client_fp);
        fclose(client_fp);
    }
    
    return 0;
}
