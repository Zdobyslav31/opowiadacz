-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: 15_kubala
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.21-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `si_static_texts`
--

DROP TABLE IF EXISTS `si_static_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `si_static_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(5) COLLATE utf8_polish_ci NOT NULL,
  `content` text COLLATE utf8_polish_ci,
  PRIMARY KEY (`id`,`language`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `si_static_texts`
--

LOCK TABLES `si_static_texts` WRITE;
/*!40000 ALTER TABLE `si_static_texts` DISABLE KEYS */;
INSERT INTO `si_static_texts` VALUES (1,'pl','<p>Cieszymy się, że chcesz wziąć udział w projekcie <strong>Opowiadacz</strong>!</p><p>Możesz po prostu czytać i śledzić losy bohaterów, ciesząc się różnorodnością zakończeń. Zachęcamy jednak, byś poszedł o krok dalej i sam dopisał własny kawałek fabuły!</p><p>Jeśli zdecydujesz się współtworzyć świat <strong>Opowiadacza</strong> wraz z innymi użytkownikami, prosimy Cię o przeczytanie poniższych <strong>zasad</strong>, które pomogą nam wszystkim zbudować spójny, żywy świat.</p>'),(2,'pl','<hr>\n                    <h3 class=\"intro-text text-center\">Dodawanie \n                        <strong>nowego rozdziału</strong>\n                    </h3>\n                    \n                    <hr>\n					<ul>\n						<li>Dodawaj, jeśli rzeczywiście masz <strong>pomysł</strong> - nie pisz na siłę czegoś, co już było.</li>\n						<li>Przeczytaj uważnie wszystkie <strong>poprzednie rozdziały</strong> - może się w nich kryć sporo ważnych szczegółów.</li>\n						<li>Zerknij na <strong>streszczenie</strong> najważniejszych wydarzeń, które zostanie Ci wyświetlone. Zwróć na nie baczna uwagę!</li>\n						<li>Nie nadużywaj <strong>formatowania</strong>, skup się raczej na treści.</li>\n						<li>Zakończ swój rozdział <strong>punktem decyzyjnym</strong>, aby umożliwić rozdzielenie się wątków.</li>\n						<li>Zanim dodasz, przejrzyj swoje opowiadanie w poszukiwaniu <strong>błędów</strong> - jeśli powstanie dalszy ciąg, nie będzie go już można usunąć.</li>\n					</ul>\n                    <hr>\n                    <h3 class=\"intro-text text-center\">Dodawanie \n                        <strong>wstępu</strong>\n                    </h3>\n                    <hr>\n					<ul>\n						<li>Do każdego rozdziału będziesz musiał dodać krótki <strong>wstęp</strong>.</li>\n						<li>Powinien on składać się maksymalnie z <strong>4 linijek</strong> tekstu.</li>\n						<li>We wstępie powinna zostać podjęta <strong>decyzja</strong>, na której skończył się poprzedni rozdział.</li>\n						<li>Nie zawieraj we wstępie <strong>spoilerów</strong> - to ma być tylko zajawka.</li>\n					</ul>\n                \n                    <hr>\n                    <h3 class=\"intro-text text-center\">Dodawanie \n                        <strong>streszczeń</strong>\n                    </h3>\n                    <hr>\n					<ul>\n						<li>Do każdego rozdziału będziesz musiał dodać również <strong>streszczenie</strong> w punktach.</li>\n						<li>Będzie się ono wyświetlać tylko użytkownikom, którzy będą tworzyć <strong>dalszy ciąg</strong> po Twoim rozdziale.</li>\n						<li>Staraj się wypunktować w nim tylko <strong>najważniejsze wydarzenia</strong>, od których będzie zależał dalszy przebieg fabuły.</li>\n						<li>Streszczenie jednego rozdziału nie może mieć wiecej niż <strong>10 punktów</strong>, każdy długości 1 linijki.</li>\n					</ul>\n                \n                    <hr>\n                    <h3 class=\"intro-text text-center\"> \n                        <strong>Edycja</strong> istniejących rozdziałów\n                    </h3>\n                    <hr>\n					<ul>\n						<li>Pamiętaj, że od treści rozdziału zależy cały <strong>dalszy ciąg</strong>, który już został napisany.</li>\n						<li>Dlatego używaj funkcji edycji <strong>wyłącznie, gdy zauważysz błędy ortograficzne i literówki</strong>.</li>\n						<li>Zmienianie fabuły na kilka rozdziałów wstecz <strong>nie jest fajne</strong> i możesz zostać za to zbanowany.</li>\n						<li>Da się edytować tylko <strong>własne rozdziały</strong>.</li>\n						<li>Pamiętaj, że nawet jeśli rozdział <strong>nie ma dalszego ciągu</strong>, ktoś może właśnie go pisać.</li>\n					</ul>\n                \n                    <hr>\n                    <h3 class=\"intro-text text-center\"> \n                        <strong>Usuwanie</strong> istniejących rozdziałów\n                    </h3>\n                    <hr>\n					<ul>\n						<li>Można usuwać tylko <strong>własne rozdziały</strong>, który nie posiadają jeszcze dalszego ciągu.</li>\n						<li>Nie usuwaj swoich rozdziałów <strong>z byle powodu</strong> - ktoś może właśnie pisać dalszy ciąg.</li>\n					</ul>\n                \n                    <hr>\n                    <h3 class=\"intro-text text-center\"> \n                        <strong>Inne</strong> zasady\n                    </h3>\n                    <hr>\n					<ul>\n						<li>Nie wolno używać <strong>wulgaryzmów, błędów językowych i innych rzeczy powszechnie uznawanych za złe</strong>, chyba że jest to w pełni umotywowane fabułą.</li>\n						<li>Administrator może <strong>z uzasadnionych przyczyn</strong> usunąć użytkownika bądź jego rozdziały, nie musząc ich o tym informować.</li>\n					</ul>'),(3,'pl','<p><strong>Opowiadacz</strong> to witryna umożliwiająca użytkownikom tworzenie wspólnego, wielowątkowego opowiadania. Jedynie pierwszy rozdział opowiadania jest wspólny, potem fabuła się rozgałęzia. Każdy użytkownik może kontynuować istniejące wątki lub dopisać swoje własne, całkowicie niezależne rozdziały.</p>\n                    <p>Projekt powstał w ramach zaliczenia przedmiotu System Interakcyjny na kierunku Elektroniczne Przetwarzanie Informacji na Uniwersytecie Jagiellońskim, w czerwcu 2017.</p>\n                    <p>Autorem projektu jest Jędrzej Kubala, wykorzystano w nim fremework <a href=\"https://silex.sensiolabs.org/\">Silex</a>, temat Business-Casual ze strony <a href=\"https://startbootstrap.com/\">Start Bootstrap</a> oraz obrazy dostępne na licencji Public Domain.</p>');
/*!40000 ALTER TABLE `si_static_texts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-09-06 19:58:31
