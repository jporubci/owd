CC := gcc
CFLAGS := -Wall
INC := include
SRC := src
EXE := bin
TARGETS := server client

all: $(TARGETS)

%: $(SRC)/%.c $(INC)/%.h
	$(CC) $(CFLAGS) -o $(EXE)/$@ $^

clean:
	rm -f $(foreach target,$(TARGETS),$(addprefix $(EXE)/,$(target)))
