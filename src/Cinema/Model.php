<?php

namespace Cinema;

/**
 * Représente le "Model", c'est à dire l'accès à la base de
 * données pour l'application cinéma basé sur MySQL
 */
class Model
{
    protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host,
                $user,
                $password
            );
        } catch (\PDOException $error) {
            die('Unable to connect to database.');
        }
        $this->pdo->exec('SET CHARSET UTF8');
    }

    protected function execute(\PDOStatement $query, array $variables = array())
    {
        if (!$query->execute($variables)) {
            $errors = $query->errorInfo();
            throw new ModelException($errors[2]);
        }

        return $query;
    }

    /**
     * Récupère un résultat exactement
     */
    protected function fetchOne(\PDOStatement $query)
    {
        if ($query->rowCount() != 1) {
            return false;
        } else {
            return $query->fetch();
        }
    }

    /**
     * Requête SQL pour obtenir un film
     */
    protected function getFilmSQL()
    {
        return
            'SELECT films.image, films.id, films.nom, films.description, genres.nom as genre_nom FROM films
             INNER JOIN genres ON genres.id = films.genre_id ';
    }

    /**
     * Récupère la liste des films
     */
    public function getFilms()
    {
        $sql = $this->getFilmSQL();

        return $this->execute($this->pdo->prepare($sql));
    }

    /**
     * Récupère un film
     */
    public function getFilm($FilmId)
    {
        $sql = $this->getFilmSQL().'WHERE films.id = '.$FilmId;

        $query = $this->pdo->prepare($sql);
        $query->execute(array('id' => $FilmId));

        return $this->fetchOne($query);
    }

    /**
    * Récupérer tout les films d'un genre
    */
    public function getFilmsParGenre($GenreId)
    {
        $sql = getFilmSQL()."WHERE genres.id = ".$GenreId;

        $query = $this->pdo->prepare($sql);

        return $query->execute(array('id' => $GenreId));

    }


    /**
    * Requête SQL pour récupérer le casting d'un film
    */
    protected function getCastingSQL()
    {
      return 'SELECT acteurs.nom, acteurs.prenom ,acteurs.image, roles.role FROM roles
              INNER JOIN acteurs ON roles.acteur_id = acteurs.id
              INNER JOIN films ON roles.film_id = films.id ';
    }

    /**
     * Récupérer le casting d'un film
     */
    public function getCasting($filmId)
    {

      $sql = $this->getCastingSQL().'WHERE films.id = '.$filmId;

        return $this->execute($this->pdo->prepare($sql));
    }

    /**
    * Requête SQL pour ajouter une critique
    */
    public function setCritiqueSQL($nom , $critique , $note , $filmId)
    {
      return "INSERT INTO critiques (nom,commentaire,note,film_id) VALUES ('".$nom."','".$critique."','".$note."','".$filmId."')";
    }

    /**
    * Ajouter une critique
    */
    public function setCritique($post , $filmId)
    {
        $nom = $note = $critique = "vide";

      // Tri des données du formulaire $post
      foreach ($post as $champPost => $valeur)
      {
            if($champPost == 'nom')
            {
                $nom = $valeur;
            }
            if($champPost == 'note')
            {
                $note = $valeur;
            }
            if($champPost == 'critique')
            {
                $critique = $valeur;
            }
      }

      // Création de la requête
      $sql = setCritiqueSQL($nom,$critique,$note,$filmId);

      // Préparation de la requête
        $req = $this->pdo->prepare($sql);

        // Exécution de la requête
        $req->execute(array("nom" => $nom,"commentaire" => $critique,"note" => $note,"film_id" => $filmId));

        $resultat = $req->fetchAll();

    }

    /**
    * Requête SQL pour récupérer les critiques
    */
    protected function getCritiquesSQL()
    {
      return 'SELECT critiques.nom, critiques.commentaire ,critiques.note FROM critiques
              INNER JOIN films ON critiques.film_id = films.id ';
    }

    /**
    * Récupérer les critiques d'un film
    */
    public function getCritiques($filmId)
    {
      $sql = $this->getCritiquesSQL().'WHERE critiques.film_id = '.$filmId ;
      $query = $this->pdo->prepare($sql);

      return $query->execute(array('film_id' => $filmId));
    }

    /**
     * Requête SQL permettant de récuperer les genres de films
     */
    public function getGenresSQL()
    {
      return
          'SELECT genres.nom, COUNT(*) as nb_films FROM genres '.
          'INNER JOIN films ON films.genre_id = genres.id '.
          'GROUP BY genres.id';
    }

    /**
     * Genres
     */
    public function getGenres()
    {
        $sql = getGenresSQL();

        return $this->execute($this->pdo->prepare($sql));
    }

    /**
    * Requête SQL pour obtenir la liste des meilleurs films classés par note moyenne
    */
    protected function getMeilleursFilmSQL()
    {
      return 'SELECT films.image, films.id, films.nom, AVG(critiques.note) as moyenneCritiqueFilm
              FROM films
              INNER JOIN critiques ON films.id = critiques.film_id
              GROUP BY films.nom
              ORDER BY moyenneCritiqueFilm DESC';
    }

    /**
    * Récupèrer la liste des meilleurs films
    */
    public function getMeilleursFilms()
    {
      $sql = $this->getMeilleursFilmSQL();
      return $this->execute($this->pdo->prepare($sql));
    }

}
