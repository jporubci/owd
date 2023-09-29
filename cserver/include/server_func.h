#ifndef SERVER_FUNC_H
#define SERVER_FUNC_H

#define TEMPLATES_DIR "templates"

void usage(int status);

/* Get server socket file descriptor from port */
int socket_listen(const char* port);

/* Get FILE* for client */
FILE* accept_client(int server_socket_fd);

/* Handle client request */
void handle_request(FILE* client_fp);

#endif
