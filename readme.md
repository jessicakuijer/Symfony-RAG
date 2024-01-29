## Project

### Description
This project is a simple example of how to use a RAG system with the combination of Symfony, LLphant and openAI.

### Requirements
- PHP 8.0
- Composer
- Symfony CLI
- OpenAI account with an API key

### Installation
1. Clone the project
2. Run `composer install`
3. No need for a database
4. Create a .env.local file and add your openAI key in it.
5. Export your openAI key as an environment variable `OPENAI_API_KEY`.  
You can generate your key in your OpenAI api platform keys settings then run:
```
export OPENAI_API_KEY=sk-XXXXXX
```  
if you're using zsh, you can add this line to your .zshrc file to make it permanent as well for your terminal sessions.  
Same for .bashrc if you're using bash.

6. Run `symfony serve-d` and go to `localhost:8000`

### Usage  
Launch the command 'symfony console GenerateEmbeddings' to generate the emmbeddings for the document of your choice in /public folder.  
It can take some times, you can change the number of words to generate in the command file.  Then you will have a result as a file in root project named 'documents-vectorStore.json' . 
This file will be used with the question to generate the answer from OpenAI.

### Credits
This project is based on the work of [LLphant's github page](https://github.com/theodo-group/LLPhant) and OpenAI.