#Fichier de création de la base
CREATE DATABASE IF NOT EXISTS quiserapresident_fr;
use quiserapresident_fr;

CREATE TABLE IF NOT EXISTS tweet
(
    id INT NOT NULL AUTO_INCREMENT,
    id_tweet VARCHAR(20) NOT NULL,
    content VARCHAR(500) NOT NULL,
    nb_like INT(11) NOT NULL,
    nb_view INT(11) NOT NULL,
    author_description VARCHAR(500) NOT NULL,
    author_certified BOOLEAN NOT NULL,
    author_nb_followers INT(11) NOT NULL,
    author_is_politic BOOLEAN NOT NULL,
    candidat VARCHAR(100) NOT NULL,
    main_sentiment VARCHAR(10),
    main_sentiment_value FLOAT(4,2),
    sentiments_value VARCHAR(1000) NOT NULL,
    last_update DATETIME NOT NULL,
    at DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS community
(
    id INT NOT NULL AUTO_INCREMENT,
    candidat VARCHAR(100) NOT NULL,
    twitter_follower INT(11) NOT NULL,
    facebook_like INT(11) NOT NULL,
    youtube_views INT(11) NOT NULL,
    at DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS news
(
    id INT NOT NULL AUTO_INCREMENT,
    candidat VARCHAR(100) NOT NULL,
    media VARCHAR(100) NOT NULL,
    title VARCHAR(100) NOT NULL,
    content VARCHAR(100000) NOT NULL,
    main_sentiment_title VARCHAR(10) NOT NULL,
    main_sentiment_value_title FLOAT(4,2) NOT NULL,
    sentiments_value_title VARCHAR(1000) NOT NULL,
    main_sentiment_content VARCHAR(10) NOT NULL,
    main_sentiment_value_content FLOAT(4,2) NOT NULL,
    sentiments_value_content VARCHAR(1000) NOT NULL,
    at DATETIME NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS survey
(
    id INT NOT NULL AUTO_INCREMENT,
    candidat VARCHAR(100) NOT NULL,
    value FLOAT(5,2) NOT NULL,
    at DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS prediction
(
    id INT NOT NULL AUTO_INCREMENT,
    candidat VARCHAR(100) NOT NULL,
    score FLOAT(5,2) NOT NULL,
    at DATETIME NOT NULL,
    PRIMARY KEY (id)
);