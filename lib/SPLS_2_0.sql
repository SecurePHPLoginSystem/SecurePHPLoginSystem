CREATE TABLE IF NOT EXISTS `languages` (
  `lang_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;


INSERT INTO `languages` (`lang_id`, `name`) VALUES
(0, 'english'),
(1, 'dutch');


CREATE TABLE IF NOT EXISTS `responses` (
  `reset_key` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(11) NOT NULL,
  `secret` char(60) COLLATE utf8_unicode_ci NOT NULL,
  `request_timestamp` datetime NOT NULL,
  `request_ip` varchar(39) COLLATE utf8_unicode_ci NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`reset_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `sent_emails` (
  `email_address` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `slugs` (
  `slug_id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) DEFAULT NULL,
  `english` varchar(10000) NOT NULL,
  `dutch` varchar(10000) NOT NULL,
  PRIMARY KEY (`slug_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

INSERT INTO `slugs` (`slug_id`, `slug`, `english`, `dutch`) VALUES
(1, 'private text', 'login succeded, congrats!\n\nFrom here on the files beginning with this code:\n\nrequire("common.php");\n$db->commonCode(true);\n\nwill be protected by the script, note this piece of code has to be on EVERY page you want to protect. This code is also visible in the file ''private.php''\n\nAlso to let the system send messages to the user, enter your message in the ''slugs'' table (let''s say the error message has the slug: ''error 1'' with a more descriptive message in the language fields) and write to the system message variable like this:\n\nGet (all) the error message(s):\n$given_slugs = $db->giveSlugs(array(''error 1''));\n\nAnd write to the system message variable:\n$_SESSION[''system_message''] .= html_escape($given_slugs[''slugs''][''private text''][$db->giveLangName()]) . "<br>";\n\nAnd display it like this:\n\n$db->SystemMessage();', 'Inloggen succesvol, gefeliciteerd!\n\nVanaf nu zijn bestanden die beginnen met het volgende codefragment:\n\nrequire("common.php");\n$db->commonCode(true);\n\nbeschermd door het script, merk op dat dit fragment op ELKE pagina moet staan die u wilt beschermen. Deze code is ook te zien in het bestand ''private.php''.\n\nOm het systeem berichten naar de gebruiker te laten sturen, moet u uw bericht in de tabel ''slugs'' zetten (laten we zeggen dat een foutmelding de slug: ''error 1'' heeft met een meer beschrijvend bericht in de taal velden) en het schrijven naar de bericht variabele zoals hierna:\n\nVerkrijg (al) de foutmelding(en):\n$given_slugs = $db->giveSlugs(array(''error 1''));\n\nEn schrijf het naar de bericht variabele:\n$_SESSION[''system_message''] .= html_escape($given_slugs[''slugs''][''private text''][$db->giveLangName()]) . "<br>";\n\nEn laat het bericht zien aan de gebruiker zoals hier:\n\n$db->SystemMessage();'),
(2, 'edit acc new password notice', 'leave blank if you do not want to change your password', 'Laat leeg als u uw wachtwoord niet wilt veranderen.'),
(3, 'edit acc old password notice', 'To submit the new settings, repeat your current (old) password.', 'Om de nieuwe instellingen te bevestigen, herhaal uw huidig (oud) wachtwoord.'),
(4, 'password reset success', 'Your password has been reset!', 'Uw wachtwoord is opnieuw ingesteld!'),
(5, 'password reset fail', 'This token has already been used, expired or inactive, please request a new one', 'Dit token is al gebruikt, verlopen of inactief, vraag alstublieft een nieuwe op.'),
(6, 'no mail invalid key', 'Invalid email key. Please check the URL in your previous notification email.', 'Ongeldige email sleutel. Controleer alstublieft de URL in uw vorige notificatie mail.'),
(7, 'no mail block success', 'Your email address has been blocked in our system. You will no longer receive notification emails.', 'Uw email adres is nu geblokkeerd in ons systeem. U zult geen emails meer van ons ontvangen.'),
(8, 'no mail block fail', 'There was a technical issue. Please try again later.', 'Er is een technische fout opgetreden. Probeer het alstublieft later opnieuw.'),
(9, 'no mail missing key', 'Missing email key. Please check the URL in your previous notification email. It must have the form ', 'Missende email sleutel. Controleer alstublieft de URL in uw vorige notificatie mail. Het moet van de volgende vorm zijn '),
(10, 'empty username', 'Please enter a username.', 'Voer een gebruikersnaam in.'),
(11, 'existing username', 'This username is already in use.', 'Deze gebruikersnaam is al gebruikt.'),
(12, 'empty password', 'Please enter a password.', 'Voer een wachtwoord in.'),
(13, 'invalid email', 'Invalid E-Mail Address.', 'Ongeldig E-Mail Adres'),
(14, 'existing email', 'This email address is already registered.', 'Dit email adres is al geregistreerd.'),
(15, 'forgot password mail success', 'Unless your limit has been reached, we''ll send you an email.', 'Tenzij uw limiet is bereikt, zullen we u een email sturen.'),
(16, 'unmatching passwords', 'The two passwords didn''t match', 'De twee wachtwoorden komen niet overeen'),
(17, 'edit account success', 'You''ve successfully edited your data.', 'U heeft succesvol uw account informatie gewijzigd.'),
(18, 'incorrect password', 'The password you entered was incorrect.', 'Het ingevoerde wachtwoord is incorrect.'),
(19, 'register', 'Register', 'Registreer'),
(20, 'username', 'Username', 'Gebruikersnaam'),
(21, 'email', 'Email', 'Email'),
(22, 'password', 'Password', 'Wachtwoord'),
(23, 'forgot password', 'Forgot password', 'Wachtwoord vergeten'),
(24, 'new password', 'New password', 'Nieuw wachtwoord'),
(25, 'password reset submit', 'Reset', 'Reset'),
(26, 'logout', 'Logout', 'Log uit'),
(27, 'edit account', 'Edit account', 'Verander account gegevens'),
(28, 'logout_message', 'You''ve been successfully logged out', 'U bent succesvol uitgelogd'),
(29, 'login', 'Login', 'Log in'),
(30, 'ReCaptcha fail', 'The reCAPTCHA wasn''t entered correctly. Go back and try it again. (reCAPTCHA said: ', 'De reCAPTCHA was verkeerd ingevuld. Ga terug en probeer het nogmaals. (reCAPTCHA zei: '),
(31, 'login fail', 'Login Failed', 'Inloggen mislukt'),
(32, 'english', 'English', 'Engels'),
(33, 'dutch', 'Dutch', 'Nederlands'),
(34, 'update account', 'Update account', 'Verander account gegevens'),
(35, 'recover password', 'Recover password', 'Haal wachtwoord op');

CREATE TABLE IF NOT EXISTS `system_levels` (
  `system_level_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`system_level_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


INSERT INTO `system_levels` (`system_level_id`, `name`) VALUES
(1, 'Admin'),
(3, 'User');

CREATE TABLE IF NOT EXISTS `unsubscribed_email_addresses` (
  `email_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` char(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email_key`),
  UNIQUE KEY `email_address` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(60) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `system_level` int(11) NOT NULL DEFAULT '1',
  `start_date` datetime NOT NULL,
  `lang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `system_level` (`system_level`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_22` FOREIGN KEY (`system_level`) REFERENCES `system_levels` (`system_level_id`),
  ADD CONSTRAINT `users_ibfk_23` FOREIGN KEY (`lang`) REFERENCES `languages` (`lang_id`);