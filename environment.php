<?php
	/*
		Ce fichier défini les constantes modifiables et les options
	*/

	//On défini l'environment
	$environment = [
		'prod' => [
			//Si vrai, on active le cache
			'ACTIVATING_CACHE' => true,

			//On défini le nom de la session
			'SESSION_NAME' => 'quiserapresidentfr',

			//Configuration de la base de données
			'DATABASE_HOST' => 'localhost',
			'DATABASE_NAME' => 'quiserapresident_fr',
			'DATABASE_USER' => 'root',
			'DATABASE_PASSWORD' => 'root',
		],
		'dev' => [
			//Si vrai, on active le cache
			'ACTIVATING_CACHE' => true,

			//On défini le nom de la session
			'SESSION_NAME' => 'quiserapresidentfr',

			//Configuration de la base de données
			'DATABASE_HOST' => 'localhost',
			'DATABASE_NAME' => 'quiserapresident_fr',
			'DATABASE_USER' => 'root',
            'DATABASE_PASSWORD' => 'root',

            //Twitter credentials
            'TWITTER_CONSUMER_KEY' => '7TwekgBwMzCxJLQ8HE1MLkzQr',
            'TWITTER_CONSUMER_SECRET' => 'e3SmsqlIfx6Cu7egIZwogGJRa8DkS9oMlu6efEr2sy0Lrv4kSL',
            'TWITTER_OAUTH_TOKEN' => '848614492422451201-IV52SNG9UxAvAkIB8mQOE1rzTbk9CuI',
            'TWITTER_OAUTH_SECRET' => 'GutB6U0SFZhMyLJ4QH2xR7Kre5lkzOaLx7luT7QGc48fy',
		],
		'test' => [
			//Si vrai, on active le cache
			'ACTIVATING_CACHE' => true,

			//On défini le nom de la session
			'SESSION_NAME' => 'quiserapresidentfr',

			//Configuration de la base de données
			'DATABASE_HOST' => 'localhost',
			'DATABASE_NAME' => 'quiserapresident_fr',
			'DATABASE_USER' => 'root',
			'DATABASE_PASSWORD' => 'root',
		]
	];

