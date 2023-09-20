# C Client/Server

## How to run

1. In cserver/, enter `make`, then `./bin/server PORT` where `PORT` is a port number. Finding a valid port number that doesn't just randomly fail is hard for me, idk why, but if it doesn't work for you, just keep trying. One that I've found success with is 5036, though sometimes it fails too.
1. Open a browser and search `db8.cse.nd.edu:PORT/index` or `db8.cse.nd.edu:PORT/project`. If you get an HTTP error when trying to connect, try hosting from a different port number.
1. You can also open another terminal window and run `./bin/client db8.cse.nd.edu PORT` where `PORT` is the port number. You should see the HTTP response come in through stdout.
1. Press `CTRL+C` to kill the server.
