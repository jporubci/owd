/* client.c
 * Jozef Porubcin */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdint.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netdb.h>
#include <unistd.h>
#include <errno.h>

#include "../include/client_func.h"

int main(int argc, char *argv[]) {
    
    /* Variables */
    char *hostname = NULL;
    char *port = NULL;
    
    /* Parse command line arguments */
    for (int i = 1; i < argc && i > 0; i++) {
        
        /* --help */
        if (!strcmp(argv[i], "--help")) {
            usage(EXIT_SUCCESS);
            
        /* HOST */
        } else if (!hostname) {
            hostname = argv[i];
            
        /* PORT */
        } else if (!port) {
            port = argv[i];
            
        } else {
            usage(EXIT_FAILURE);
        }
    }
    
    if (!hostname || !port) {
        usage(EXIT_FAILURE);
    }
    
    FILE *server_fp = socket_dial(hostname, port);
    
    if (!server_fp) {
        return EXIT_FAILURE;
    }
    
    /* Send HTTP request */
    fprintf(server_fp, "POST /index HTTP/1.1\r\n");
    fprintf(server_fp, "\r\n");
    
    /* Read HTTP response */
    char response_buffer[BUFSIZ];
    while (fgets(response_buffer, BUFSIZ, server_fp)) {
        fputs(response_buffer, stdout);
    }
    
    /* close */
    fclose(server_fp);
    
    return EXIT_SUCCESS;
}
