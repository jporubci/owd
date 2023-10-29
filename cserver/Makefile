EXE := server client
FUN := _func

BIN := bin
OBJ := obj
SRC := src
INC := include

all: $(addprefix $(BIN)/, $(EXE))

# Initialize directories
init:
	@echo "Creating directories..."
	@mkdir -p $(BIN) $(OBJ) $(SRC) $(INC)

# Build executables
$(BIN)/%: $(SRC)/%.c $(OBJ)/%$(FUN).o
	@echo "Building $@..."
	@gcc -Wall -o $@ $^ -lmysqlclient

# Build object files
$(OBJ)/%.o: $(SRC)/%.c $(INC)/%.h
	@gcc -Wall -o $@ $< -c

# Delete executables and object files
clean:
	@echo "Cleaning..."
	@rm -f $(BIN)/* $(OBJ)/*.o
