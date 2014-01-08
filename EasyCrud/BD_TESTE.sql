CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `picture` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;


CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `event_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;


CREATE TABLE `empresa_evento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `evento_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  CONSTRAINT `empresa_evento_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `event` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

INSERT INTO `user` (id,name,age,picture) VALUES (1,'marcelo',23,null);
INSERT INTO `user` (id,name,age,picture) VALUES (44,'teste',33,null);
INSERT INTO `user` (id,name,age,picture) VALUES (55,'barbara aldighieri',21,null);
INSERT INTO `user` (id,name,age,picture) VALUES (56,'Guilherme gomes',23,null);



INSERT INTO `event` (id,description,date,user_id) VALUES (9,'Show do Scorpions','2010-09-19',1);
INSERT INTO `event` (id,description,date,user_id) VALUES (12,'Show do Bon Jovi','2010-10-21',1);
INSERT INTO `event` (id,description,date,user_id) VALUES (14,'Show do Rush','2010-09-16',1);
INSERT INTO `event` (id,description,date,user_id) VALUES (15,'Formatura UFPE','2010-11-26',44);
INSERT INTO `event` (id,description,date,user_id) VALUES (16,'testeFK','2010-10-13',44);
INSERT INTO `event` (id,description,date,user_id) VALUES (17,'outro teste','2010-10-05',55);
INSERT INTO `event` (id,description,date,user_id) VALUES (18,'evento teste','2010-10-21',56);
INSERT INTO `event` (id,description,date,user_id) VALUES (19,'mais um evento','2010-10-31',55);
INSERT INTO `event` (id,description,date,user_id) VALUES (20,'red hot chili peppers','2010-11-19',55);
INSERT INTO `event` (id,description,date,user_id) VALUES (21,'Fender Strato','2010-09-17',55);
INSERT INTO `event` (id,description,date,user_id) VALUES (22,'Guitarras','2010-10-25',56);
INSERT INTO `event` (id,description,date,user_id) VALUES (23,'Mais outro evento','2010-10-30',56);
INSERT INTO `event` (id,description,date,user_id) VALUES (24,'Ensaio','2010-10-20',44);
INSERT INTO `event` (id,description,date,user_id) VALUES (25,'Jam G3','2010-11-12',56);
INSERT INTO `event` (id,description,date,user_id) VALUES (26,'Php Framework','2010-10-21',1);
INSERT INTO `event` (id,description,date,user_id) VALUES (27,'EasyCrud Framework','2010-10-28',1);
INSERT INTO `event` (id,description,date,user_id) VALUES (28,'Development','2011-01-28',55);
INSERT INTO `event` (id,description,date,user_id) VALUES (29,'Php Ajax CRUD Framework','2010-04-14',1);
INSERT INTO `event` (id,description,date,user_id) VALUES (30,'Music Concert','2011-09-23',44);
INSERT INTO `event` (id,description,date,user_id) VALUES (31,'Rock N Roll','2011-03-16',56);
INSERT INTO `event` (id,description,date,user_id) VALUES (32,'Gibson Les Paul','2012-02-16',44);

INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (1,'ANAC',21);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (2,'Embratel',9);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (3,'Celo Tech',15);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (4,'Facilit',12);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (5,'Calango Dev',14);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (6,'Fender',21);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (7,'Red Hot Chili Peppers',20);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (8,'Playtech Music',22);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (9,'Sony',14);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (10,'Apple',17);
INSERT INTO `empresa_evento` (id,nome_empresa,evento_id) VALUES (11,'Deep Purple',20);


