<?php

namespace App\Command;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'GenerateEmbeddings',
    description: 'Generate embeddings for the bot to use',
)]
class GenerateEmbeddingsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title(("Hello ! Nous allons générer les embeddings de vos données."));

        $io->section("Lecture des données");
        $dataReader = new FileDataReader(__DIR__ . '/../../public/guide-bonnes-pratiques-numerique-responsable-export-version-v1.json');
        $documents = $dataReader->getDocuments();
        $io->success("Lecture des données terminée, et " . count($documents) . " documents ont été trouvés.");

        $io->section("Découpage des documents");
        $splittedDocuments = DocumentSplitter::splitDocuments($documents, 1000);
        $io->success("Découpage des documents terminé en  " . count($splittedDocuments) . " documents de 1000 mots.");
        
        $io->section("Génération des embeddings");
        $embeddingGenerator = new OpenAIEmbeddingGenerator();
        $embeddedDocuments = $embeddingGenerator->embedDocuments($splittedDocuments);
        $io->success("Génération des embeddings terminée.");

        $io->section("Sauvegarde des embeddings");
        $vectorStore = new FileSystemVectorStore();
        $vectorStore->addDocuments($embeddedDocuments);
        $io->success("Sauvegarde des embeddings terminée.");

        $io->success("Les embeddings ont été générés avec succès et stockés dans le fichier documents-vectorStore.json en racine du projet.");

        return Command::SUCCESS;
    }
}
