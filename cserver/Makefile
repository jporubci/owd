EXE := server client
FUN := _func

BIN := bin
SRC := src
OBJ := obj
INC := include

all: $(addprefix $(BIN)/, $(EXE))

# Initialize directories
init:
	@echo "Creating directories..."
	@mkdir -p $(BIN) $(SRC) $(OBJ) $(INC)

# Build executables
$(BIN)/%: $(SRC)/%.c $(OBJ)/%$(FUN).o
	@echo "Building $@..."
	@gcc -Wall -o $@ $^

# Build object files
$(OBJ)/%.o: $(SRC)/%.c $(INC)/%.h
	@gcc -Wall -c -o $@ $<

# Delete executables and object files
clean:
	@echo "Cleaning..."
	@rm -f $(BIN)/* $(OBJ)/*.o
