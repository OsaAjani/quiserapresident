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

            //Facebook credentials
            'FACEBOOK_APP_ID' => '1290641054305625',
            'FACEBOOK_APP_SECRET' => '7f682bd24817e8528b731bf07ff8e42b',

            //Youtube credentials
            'YOUTUBE_KEY' => 'AIzaSyCywCB51pGCMJJe-XQA1m4ql4Z-qDIKGk0',
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

    $candidats = [
        'melenchon' => [
            'real_name' => "Jean-Luc Mélenchon",
            'keywords' => [
                '1' => [
                    'Melenchon',
                    'Jean-Luc Melenchon',
                    'Jean Luc Melenchon',
                    '#JLM',
                    '#JLM2017',
                    '#JeanLucMelenchon',
                    '#Melenchon',
                    '#Melenchon2017',
                    '@JLMelenchon',
                    '@jlm_2017',
                ],
                '0.5' => [
                    'France Insoumise',
                    'Front de gauche',
                    '#FranceInsoumise',
                    'President du front de gauche',
                    'Candidat de la France Insoumise',
                    'Candidat du parti La France Insoumise',
                ],
            ],
            'accounts' => [
                'twitter' => [
                    '1' => [
                        '@JLMelenchon',
                    ],
                    '0.75' => [
                        '@jlm_2017'
                    ],
                ],
                'facebook' => [
                    '1' => [
                        'JLMelenchon',
                    ],
                    '0.75' => [
                        'JLM2017',
                    ],
                ],
                'youtube' => [
                    '1' => [
                        'UCk-_PEY3iC6DIGJKuoEe9bw',
                    ],
                ],
            ],
        ],

        'le pen' => [
            'real_name' => "Marine Le Pen",
            'keywords' => [
                '1' => [
                    'Lepen',
                    'Marine Lepen',
                    'Marine Le Pen',
                    'MLP',
                    '#MLP',
                    '#MLP2017',
                    '#Marine2017',
                    '#MarineLepen',
                    '#lepen',
                    '#lepen2017',
                    '@MLP_officiel',
                ],
                '0.5' => [
                    'Front National',
                    '@FN_officiel',
                    '#AuNomDuPeuple',
                    'Presidente du front national',
                    'Candidate du front national',
                    'Candidate FN',
                    'Candidate du FN',
                ],
            ],
            'accounts' => [
                'twitter' => [
                    '1' => [
                        '@MLP_officiel',
                    ],
                    '0.75' => [
                        '@FN_officiel',
                    ],
                ],
                'facebook' => [
                    '1' => [
                        'MarineLePen',
                    ],
                    '0.75' => [
                        'FN.officiel',
                    ],
                ],
                'youtube' => [
                    '0.6' => [
                        'UClaa_CwoQEmSo9Mb_M1f91g',
                    ],
                ],
            ],
        ],

        'macron' => [
            'real_name' => "Emmanuel Macron",
            'keywords' => [
                '1' => [
                    'Macron',
                    'Emmanuel Macron',
                    '#Macron',
                    '#macron2017',
                    '#emmanuelMacron',
                    '#emmanuelMacron2017',
                    '@EmmanuelMacron',
                ],
                '0.5' => [
                    '@enmarchefr',
                    'En Marche',
                    '#enMarche',
                    'President d\'en marche',
                    'Candidat d\'en marche',
                    'Candidat du parti En Marche',
                ],
            ],
            'accounts' => [
                'twitter' => [
                    '1' => [
                        '@EmmanuelMacron',
                    ],
                    '0.75' => [
                        '@enmarchefr',
                    ],
                ],
                'facebook' => [
                    '1' => [
                        'EmmanuelMacron',
                    ],
                    '0.75' => [
                        'EnMarche',
                    ],
                ],
                'youtube' => [
                    '1' => [
                        'UCJw8np695wqWOaKVhFjkRyg',
                    ],
                ],
            ],
        ],
        
        'fillon' => [
            'real_name' => "François Fillon",
            'keywords' => [
                '1' => [
                    'Fillon',
                    'François Fillon',
                    '#Fillon',
                    '#FrancoisFillon',
                    '#FrançoisFillon',
                    '#Fillon2017',
                    '#FrancoisFillon2017',
                    '#FrançoisFillon2017',
                    '@FrancoisFillon',
                ],
                '0.5' => [
                    'Les Républicains',
                    '#LR',
                    'LR',
                    'Candidat du parti Les Républicains',
                    'Candidat des Républicains',
                    'Candidat de Les Républicains',
                    'Candidat Les Républicains',
                    'Candidat LR',
                ],
            ],
            'accounts' => [
                'twitter' => [
                    '1' => [
                        '@FrancoisFillon',
                    ],
                    '0.75' => [
                        '@Fillon2017_fr',
                    ],
                ],
                'facebook' => [
                    '1' => [
                        'FrancoisFillon',
                    ],
                    '0.75' => [
                        'Fillon2017',
                    ],
                ],
                'youtube' => [
                    '1' => [
                        'UCp1R4BFJrKw34PfUc3GDLkw',
                    ],
                ],
            ],
        ],
        
        'hamon' => [
            'real_name' => 'Benoît Hamon',
            'keywords' => [
                '1' => [
                    'Hamon',
                    'Benoit Hamon',
                    '#Hamon2017',
                    '#BenoitHamon',
                    '#Hamon',
                    '#BenoitHamon2017',
                    '@benoithamon',
                    '@AvecHamon2017',
                ],
                '0.5' => [
                    'Parti Socialiste',
                    '#PartiSocialiste',
                    '#PS',
                    'Candidat du parti socialiste',
                    'Candidat du PS',
                    'Candidat PS',
                ],
            ],
            'accounts' => [
                'twitter' => [
                    '1' => [
                        '@benoithamon',
                    ],
                    '0.75' => [
                        '@AvecHamon2017'
                    ],
                ],
                'facebook' => [
                    '1' => [
                        'hamonbenoit',
                    ],
                    '0.75' => [
                        'avechamon',
                    ],
                ],
                'youtube' => [
                    '1' => [
                        'UCcMryUp6ME3BvP2alkS1dKg',
                    ],
                ],
            ],
        ],
    ];
