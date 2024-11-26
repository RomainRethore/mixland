<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    /**
     * @var string $targetDirectory Le répertoire cible où les fichiers seront téléchargés.
     */
    private $targetDirectory;

    /**
     * @var SluggerInterface $slugger L'interface utilisée pour générer des noms de fichiers sécurisés.
     */
    private $slugger;

    /**
     * Constructeur de la classe FileUploader.
     *
     * @param string $targetDirectory Le répertoire cible où les fichiers seront téléchargés.
     * @param SluggerInterface $slugger L'interface utilisée pour générer des noms de fichiers sécurisés.
     */
    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    /**
     * Télécharge un fichier et retourne son nouveau nom.
     *
     * @param UploadedFile $file Le fichier à télécharger.
     * @return string|null Le nom du fichier téléchargé ou null en cas d'échec.
     */
    public function upload(UploadedFile $file)
    {
        // Récupère le nom original du fichier sans l'extension.
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Génère un nom de fichier sécurisé.
        $safeFilename = $this->slugger->slug($originalFilename);

        // Crée un nom de fichier unique avec l'extension originale.
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            // Déplace le fichier vers le répertoire cible.
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            // Gère l'exception si quelque chose se passe mal pendant le téléchargement.
            return null;
        }

        // Retourne le nom du fichier téléchargé.
        return $fileName;
    }

    /**
     * Retourne le répertoire cible où les fichiers sont téléchargés.
     *
     * @return string Le répertoire cible.
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
