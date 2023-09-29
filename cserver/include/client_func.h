#ifndef CLIENT_FUNC_H
#define CLIENT_FUNC_H

void usage(int status);

FILE* socket_dial(const char* hostname, const char* port);

#endif
