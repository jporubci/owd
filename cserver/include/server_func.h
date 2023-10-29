#ifndef SERVER_FUNC_H
#define SERVER_FUNC_H

#include <mysql/mysql.h>

#define TEMPLATES_DIR "templates"

void usage(int);

/* Get server socket file descriptor from port */
int socket_listen(const char *);

/* Get FILE* for client */
FILE * accept_client(int);

/* Handle client request */
void handle_request(FILE *, MYSQL *);

#endif
