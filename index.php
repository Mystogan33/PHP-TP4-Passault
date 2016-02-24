<?php

$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application;
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Fait remonter les erreurs
$app['debug'] = true;

$app['model'] = new Cinema\Model(
    'info-arie',  // Hôte
    'info_vgay001',    // Base de données
    'vgay001',    // Utilisateur
    'L5M9npSiqU5W'     // Mot de passe
);

// Page d'accueil
$app->match('/', function() use ($app) {
    return $app['twig']->render('home.html.twig');
})->bind('home');

// Liste des films
$app->match('/films', function() use ($app) {
    return $app['twig']->render('films.html.twig', array(
        'films' => $app['model']->getFilms()
    ));
})->bind('films');

// Fiche film
$app->match('/film/{id}', function($id) use ($app) {
    $request = $app['request'];
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('nom') && $post->has('note') && $post->has('critique')) {
            $app['model']->setCritique($post,$id);
        }
    }

    return $app['twig']->render('film.html.twig', array(
        'film' => $app['model']->getFilm($id),
        'casting' => $app['model']->getCasting($id),
        'critiques' => $app['model']->getCritiques($id)
    ));
})->bind('film');

// Meilleurs films
$app->match('/meilleursFilms', function() use ($app) {
    return $app['twig']->render('MeilleursFilms.html.twig', array(
        'meilleursFilms' => $app['model']->getMeilleursFilms()
    ));
})->bind('meilleursFilms');

// Genres
$app->match('/genres', function() use ($app) {
    return $app['twig']->render('genres.html.twig', array(
        'genres' => $app['model']->getGenres()
    ));
})->bind('genres');

$app->run();
