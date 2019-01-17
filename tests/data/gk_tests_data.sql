USE `geokrety-test-db`;

# users
INSERT INTO `gk-users` (`userid`, `user`, `haslo`, `haslo2`, `email`, `email_invalid`, `joined`, `wysylacmaile`, `ip`, `timestamp`, `lang`, `lat`, `lon`, `promien`, `country`, `godzina`, `statpic`, `ostatni_mail`, `ostatni_login`, `secid`) VALUES
(38785,'boly38','','$2a$11$LGPsDqrB9AmcMgIL5FeLUe.1eINrrq0OGay.5sO6W/A/JMTxRUUjK','',0,'2019-01-17 12:22:47',UNHEX('31'),'','2019-01-17 12:37:21','fr',NULL,NULL,0,NULL,21,1,NULL,'2019-01-17 12:37:21','Ih3BwD8Qbb3siYi1G6yiUOqbnL1iKBowfeLvreRjd9FZ12Uk2C4G8v6bhdy6trYtA0bEZM4dHzLw5DtnPuNrddBZ9jaTgFz7YS8muNzz0M6Gozb1jOveAAUgnlsZ2u1R');


# geokrets
INSERT INTO `gk-geokrety` (`id`, `nr`, `nazwa`, `opis`, `owner`, `data`, `droga`, `skrzynki`, `zdjecia`, `ost_pozycja_id`, `ost_log_id`, `hands_of`, `missing`, `typ`, `avatarid`, `timestamp_oc`, `timestamp`) VALUES
(67914,'CV6WHA','Perceval','C&rsquo;est pas faux.',38785,'2019-01-17 12:34:35',1336,2,0,1349548,1349548,38785,0,'0',0,'2019-01-17 12:37:20','2019-01-17 12:37:20');

# moves (ruchy)
INSERT INTO `gk-ruchy` (`ruch_id`, `id`, `lat`, `lon`, `alt`, `country`, `droga`, `waypoint`, `data`, `data_dodania`, `user`, `koment`, `zdjecia`, `komentarze`, `logtype`, `username`, `timestamp`, `app`, `app_ver`) VALUES
(1349547,67914,45.34310,5.97807,-7000,'fr',0,'GC_PAS_LA','2019-01-17 13:35:00','2019-01-17 13:35:19',38785,'Donc, pour r&eacute;sumer, je suis souvent victime des colibris, sous-entendu des types qu&rsquo;oublient toujours tout. Euh, non&hellip; Bref, tout &ccedil;a pour dire, que je voudrais bien qu&rsquo;on me consid&egrave;re en tant que Tel.',0,0,'5','','2019-01-17 12:35:19','www',''),
(1349548,67914,52.15340,21.05390,-7000,'pl',1336,'GC_EN_éTé','2019-01-17 13:37:00','2019-01-17 13:37:20',38785,'Ah ! oui... j\' l\'ai fait trop fulgurant, l&agrave;. &Ccedil;a va ?',0,0,'5','','2019-01-17 12:37:20','www','');