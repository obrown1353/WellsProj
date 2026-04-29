-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 03:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wellsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `dbcheckout`
--

CREATE TABLE `dbcheckout` (
  `checkout_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` text NOT NULL,
  `checkout_date` date NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dblogs`
--

CREATE TABLE `dblogs` (
  `log_id` int(11) NOT NULL,
  `log_type` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `log_time` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dblogs`
--

INSERT INTO `dblogs` (`log_id`, `log_type`, `message`, `log_time`) VALUES
(3, 'checkouts', 'Hannah Lydell has checked out Hoop Kings', '2026-04-12'),
(4, 'checkouts', 'Hannah\'s Lydell has checked out Hoop Kings', '2026-04-12'),
(5, 'checkouts', 'Hannah\'s Lydell has returned Hoop Kings', '2026-04-12'),
(6, 'catalog', 'Material: test has been added to catalog', '2026-04-12'),
(7, 'catalog', 'Material: test has been added to catalog', '2026-04-12'),
(8, 'catalog', 'Material: a has been added to catalog', '2026-04-12'),
(9, 'catalog', 'Material: a has been added to catalog', '2026-04-12'),
(10, 'catalog', 'Material: test has been added to catalog', '2026-04-12'),
(11, 'catalog', 'Material: a has been added to catalog', '2026-04-12'),
(12, 'checkouts', 'Hannah Lydell has checked out Hoop Kings', '2026-04-12'),
(13, 'checkouts', 'Hannah Lydell has returned Hoop Kings', '2026-04-12'),
(14, 'checkouts', 'Hannah Lydell has checked out Hoop Kings', '2026-04-12'),
(15, 'checkouts', 'Hannah Lydell has returned Hoop Kings', '2026-04-12'),
(16, 'catalog', 'Material: test1 has been added to catalog', '2026-04-12'),
(17, 'catalog', 'Material: test2 has been added to catalog', '2026-04-12'),
(18, 'catalog', 'Material: a has been added to catalog', '2026-04-12'),
(19, 'catalog', 'Material: a has been added to catalog', '2026-04-14'),
(20, 'catalog', 'Material: test has been added to catalog', '2026-04-14'),
(21, 'catalog', 'Material: a has been added to catalog', '2026-04-15'),
(22, 'checkouts', 'Hannah Lydell has returned Let\'s Read Biography: Barbara Jordan', '2026-04-15'),
(23, 'checkouts', 'Hannah Lydell has checked out Let\'s Read Biography: Barbara Jordan', '2026-04-15'),
(24, 'checkouts', 'Hannah Lydell has returned Let\'s Read Biography: Barbara Jordan', '2026-04-15'),
(25, 'catalog', 'Material: a has been added to catalog', '2026-04-15'),
(26, 'system', 'hannahtest has been added as a worker', '2026-04-20'),
(27, 'system', 'hlydell has been been removed from staff', '2026-04-20'),
(28, 'checkouts', 'Hannah Lydell has checked out Let\'s Read Biography: Benito Juárez', '2026-04-20'),
(29, 'checkouts', 'Hannah Lydell has returned Let\'s Read Biography: Benito Juárez', '2026-04-20'),
(30, 'catalog', 'Material: test has been updated', '2026-04-20'),
(31, 'catalog', 'Material: test has been updated', '2026-04-20'),
(32, 'catalog', 'Material: a has been updated', '2026-04-20'),
(33, 'catalog', 'Material: a2 has been updated', '2026-04-20'),
(34, 'catalog', 'Material: a has been updated', '2026-04-20'),
(35, 'catalog', 'Material: a has been updated', '2026-04-20'),
(36, 'catalog', 'Material: a2 has been updated', '2026-04-20'),
(37, 'catalog', 'Material: a2 has been updated', '2026-04-20'),
(38, 'catalog', 'Material: a2 has been updated', '2026-04-20');

-- --------------------------------------------------------

--
-- Table structure for table `dbmaterials`
--

CREATE TABLE `dbmaterials` (
  `material_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `location` text NOT NULL,
  `resource_type` text NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `author` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `copy_capacity` int(11) NOT NULL,
  `copy_instock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dbmaterials`
--

INSERT INTO `dbmaterials` (`material_id`, `name`, `location`, `resource_type`, `isbn`, `author`, `description`, `copy_capacity`, `copy_instock`) VALUES
(1, 'Hoop Kings', 'Holiday', 'Children\'s Literature', '9780763635602', 'Smith, Charles R., Jr.', '', 1, 1),
(2, 'Soccer Stars', 'General Nonfiction', 'Children\'s Literature', '9780593886151', 'DiLeo, Travis', '', 1, 1),
(3, 'Mouse Views', 'General Nonfiction', 'Children\'s Literature', '9780823410088', 'McMillan, Bruce', NULL, 1, 1),
(4, 'Buried Alive!', 'General Nonfiction', 'Children\'s Literature', '9780547707785', 'Scott, Elaine', NULL, 1, 1),
(5, 'Let\'s Read Biography: Barbara Jordan', 'General Nonfiction', 'Children\'s Literature', '9780395813362', NULL, NULL, 1, 1),
(6, 'Let\'s Read Biography: Benito Juárez', 'General Nonfiction', 'Children\'s Literature', ' 9780395813379', NULL, NULL, 1, 1),
(7, 'Let\'s Read Biography: Antonia Novello', 'General Nonfiction', 'Children\'s Literature', '9780395813423', NULL, NULL, 1, 1),
(20, 'a2', 'aa', 'a', '', '', '', 1, 1),
(21, 'test', 'test', 'test', '', '', '', 1, 1),
(22, 'a', 'a', 'a', '', '', '', 1, 1),
(23, 'a', 'a', 'a', 'a', '', '', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `dbpersons`
--

CREATE TABLE `dbpersons` (
  `id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` text DEFAULT NULL,
  `first_name` text NOT NULL,
  `last_name` text DEFAULT NULL,
  `street_address` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zip_code` text DEFAULT NULL,
  `phone1` varchar(12) DEFAULT NULL,
  `over21` tinyint(1) DEFAULT NULL,
  `phone1type` text DEFAULT NULL,
  `emergency_contact_phone` varchar(12) DEFAULT NULL,
  `emergency_contact_phone_type` text DEFAULT NULL,
  `birthday` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `email_prefs` enum('true','false') DEFAULT NULL,
  `emergency_contact_first_name` text DEFAULT NULL,
  `contact_num` varchar(255) DEFAULT 'n/a',
  `emergency_contact_relation` text DEFAULT NULL,
  `contact_method` text DEFAULT NULL,
  `type` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `password` text DEFAULT NULL,
  `affiliation` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT NULL,
  `emergency_contact_last_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dbpersons`
--

INSERT INTO `dbpersons` (`id`, `start_date`, `first_name`, `last_name`, `street_address`, `city`, `state`, `zip_code`, `phone1`, `over21`, `phone1type`, `emergency_contact_phone`, `emergency_contact_phone_type`, `birthday`, `email`, `email_prefs`, `emergency_contact_first_name`, `contact_num`, `emergency_contact_relation`, `contact_method`, `type`, `status`, `notes`, `password`, `affiliation`, `branch`, `archived`, `emergency_contact_last_name`) VALUES
('acarmich@mail.umw.edu', '2025-12-01', 'John', 'Doe', NULL, 'Fredericksburg', 'VA', NULL, '5555555555', 0, '', '', '', '', 'acarmich@mail.umw.edu', 'false', '', '', '', '', 'volunteer', '', '', '$2y$10$1CDYmdifcx5rfR80Ui8WLuM2ldqc4DTJiFbK1JMSLycE/0lLKPJUS', 'Family', 'Air Force', NULL, ''),
('ameyer3', '2025-03-26', 'Aidan', 'Meyer', '1541 Surry Hill Court', 'Charlottesville', 'VA', '22901', '4344222910', 0, 'home', '4344222910', 'home', '2003-08-17', 'aidanmeyer32@gmail.com', NULL, 'Aidan', 'n/a', 'Father', NULL, 'volunteer', 'Active', NULL, '$2y$10$0R5pX4uTxS0JZ4rc7dGprOK4c/d1NEs0rnnaEmnW4sz8JIQVyNdBC', NULL, NULL, 0, 'Meyer'),
('armyuser', '2025-11-30', 'Army', 'Active Duty', NULL, 'FXBG', 'VA', NULL, '3243242342', 0, '', '', '', '', 'example@example.com', 'false', '', '', '', '', '', '', '', '$2y$10$kdxwMq.xaGsYvl8gY/8l3.xwu9ABEhWernkR6kmro9QtNvvEjqPFi', 'Active duty', 'Army', NULL, ''),
('BobVolunteer', '2025-04-29', 'Bob', 'SPCA', '123 Dog Ave', 'Dogville', 'VA', '54321', '9806761234', 0, 'home', '1234567788', 'home', '2020-03-03', 'fred54321@gmail.com', NULL, 'Luke', 'n/a', 'Bff', NULL, 'volunteer', 'Active', NULL, '$2y$10$4wUwAW0yoizxi5UFy1/OZu.yfYY7rzUsuYcZCdvfplLj95r7OknvG', NULL, NULL, 0, 'Blair'),
('Britorsk', '2026-02-05', 'Brian', 'Prelle', NULL, 'KING GEORGE', 'VA', NULL, '5402076085', 0, '', '', '', '', 'brian2@prelle.net', 'false', '', '', '', '', '', '', '', '$2y$10$q9wFQJ/guFjlUnR7IfJt/.MRf5bDfK8FxebznfRt644twzYepM/bC', 'Family', 'Air Force', NULL, ''),
('exampleuser', '2025-10-20', 'example', 'user', '', 'test', 'VA', '', '2344564645', 0, '', '', '', '', 'example@test.com', NULL, '', 'n/a', '', NULL, 'v', 'Active', NULL, '$2y$10$J0NgBjoyg9F6YMyy/qQpv.f94OLM2r19sY80BZMhMdcl38SN5vdre', NULL, NULL, 0, ''),
('fakename', '2025-12-10', 'fake', 'name', NULL, 'realtown', 'VA', NULL, '5555555555', 0, '', '', '', '', 'fakeemail@email.email.com', 'true', '', '', '', '', '', '', '', '$2y$10$4h8ImkaTyMprwU3SzWrWx./NBI7yClMoqCkEbYJuA1/9cb0tSlUI.', 'Civilian', 'Marine Corp', NULL, ''),
('firstName', '2025-12-10', 'firstName', 'lastName', NULL, 'homeTown', 'TX', NULL, '5555555555', 0, '', '', '', '', 'realemail@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$og/aLBzrg195Qph9d2M/DuX2DIPhP.0sVT3vtu/WUpGCse8B.k71m', 'Civilian', 'Navy', NULL, ''),
('gabriel', '2026-02-02', 'Gabriel', 'Courtney', NULL, 'King George', 'VA', NULL, '5404295285', 0, '', '', '', '', 'gabrielcourtney04@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$4uvfLFyFy9Ui1i8Q1r0MWuFRGYfgvVh4.iUtvXksfVJm4pZpxxtSq', 'Active duty', 'Space Force', NULL, ''),
('hannahtest', '2026-04-20', 'Hannah', 'Lydell', NULL, '', '', NULL, '', 0, '', '', '', '', 'lydellhannah@gmail.com', 'false', '', '', '', '', 'worker', 'Active', '', '$2y$10$Wdw/e8L.ENEo47e1bN5Dk.qD21xKR8L8hjhDEgOQEnluw1d7iTgO6', '', '', NULL, ''),
('japper', '2026-02-02', 'Jennifer', 'Polack', NULL, 'Fredericksburg', 'VA', NULL, '5406541318', 0, '', '', '', '', 'jenniferpolack@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$mJzI.UGPGUmYgo7HxTamkeKlsmajzLwXM6su4NdxuHYHZXIGnb0xm', 'Family', 'Marine Corp', NULL, ''),
('Jlipinsk', '2025-12-03', 'Jake', 'Lipinski', NULL, 'Williamsburg', 'VA', NULL, '7577903325', 0, '', '', '', '', 'jlipinsk@mail.umw.edu', 'true', '', '', '', '', '', '', '', '$2y$10$qz33T0Sq760IITyYajCYOeWlHR/7sRJH.U609EUkF3R5zRiWWddkG', 'Civilian', 'Army', NULL, ''),
('johnDoe123', '2026-02-07', 'John', 'Doe', NULL, 'Fredericksburg', 'VA', NULL, '2345678910', 0, '', '', '', '', 'test@email.com', 'false', '', '', '', '', '', '', '', '$2y$10$LTVIuLeSZ4ferdNOe0JdTedaFHqFuEOAz7HDCQuZ4PG9kZrRJc7xS', 'Active duty', 'Navy', NULL, ''),
('lukeg', '2025-04-29', 'Luke', 'Gibson', '22 N Ave', 'Fredericksburg', 'VA', '22401', '1234567890', 0, 'cellphone', '1234567890', 'cellphone', '2025-04-28', 'volunteer@volunteer.com', NULL, 'NoName', 'n/a', 'Brother', NULL, 'volunteer', 'Active', NULL, '$2y$10$KsNVJYhvO5D287GpKYsIPuci9FnL.Eng9R6lBpaetu2Y0yVJ7Uuiq', NULL, NULL, 0, 'YesName'),
('maddiev', '2025-04-28', 'maddie', 'van buren', '123 Blue st', 'fred', 'VA', '12343', '1234567890', 0, 'cellphone', '1234567819', 'cellphone', '2003-05-17', 'mvanbure@mail.umw.edu', NULL, 'mommy', 'n/a', 'mom', NULL, 'volunteer', 'Active', NULL, '$2y$10$0mv3.e6gjqoIg.HfT5qVXOsI.Ca5E93DAy8BnT124W1PvMDxpfoxy', NULL, NULL, 0, 'van buren'),
('michael_smith', '2025-03-16', 'Michael', 'Smith', '789 Pine Street', 'Charlottesville', 'VA', '22903', '4345559876', 0, 'mobile', '4345553322', 'work', '1995-08-22', 'michaelsmith@email.com', NULL, 'Sarah', '4345553322', 'Sister', 'email', 'volunteer', 'Active', '', '$2y$10$XYZ789xyz456LMN123DEF', NULL, NULL, 0, 'Smith'),
('michellevb', '2025-04-29', 'Michelle', 'Van Buren', '1234 Red St', 'Freddy', 'VA', '22401', '1234567890', 0, 'cellphone', '0987654321', 'cellphone', '1980-08-18', 'michelle.vb@gmail.com', NULL, 'Madison', 'n/a', 'daughter', NULL, 'volunteer', 'Active', NULL, '$2y$10$bkqOWUdIJoSa6kZoRo5KH.cerZkBQf74RYsponUUgefJxNc8ExppK', NULL, NULL, 0, 'Van Buren'),
('navyspouse', '2025-11-30', 'Navy', 'Spouse', NULL, 'FXBG', 'VA', NULL, '3543534543', 0, '', '', '', '', 'example@example.com', 'false', '', '', '', '', '', '', '', '$2y$10$nqoIFq4ru0k1wLkg0E/rfupwez.x1Gg6ldEuKgC.jIQemgCEuDzkG', 'Family', 'Navy', NULL, ''),
('olivi', '2026-02-04', 'Olivia', 'Blue', NULL, 'Fredericksburg', 'VA', NULL, '1112223333', 0, '', '', '', '', 'oliviablue@gmail.com', 'false', '', '', '', '', '', '', '', '$2y$10$ew4nuUYBtx6.CbNBezMTYuAQGaxMJgxIs4I3uIx05Sb7SqxKHJO2S', 'Family', 'Marine Corp', NULL, ''),
('test_acc', '2025-04-29', 'test', 'test', 'test', 'test', 'VA', '22405', '5555555555', 0, 'cellphone', '5555555555', 'cellphone', '2003-03-03', 'test@gmail.com', NULL, 'test', 'n/a', 't', NULL, 'volunteer', 'Active', NULL, '$2y$10$kpVA41EXvoJyv896uDBEF.fHCPmSlkVSaXjHojBl7DqbRnEm//kxy', NULL, NULL, 0, 'test'),
('test_person', '2025-10-26', 'Testina', 'Tester', NULL, 'Testville', 'VA', NULL, '5555555555', 0, 'mobile', NULL, NULL, '1980-08-18', 'testing@gmail.com', 'false', NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, '$2y$10$blAQaBgCChBv5qRtBFVVAe1m6gIfwPf/wJ8HxzLFTYiY3aWpvaW8e', 'civilian', 'Army', NULL, NULL),
('test_persona', '2025-10-28', 'Testana', 'Tester', NULL, 'Testinaville', 'VA', NULL, '5555555555', 0, NULL, NULL, NULL, NULL, 'testerana@gmail.com', 'true', NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, '$2y$10$s90qlNAJE9EbgLhZbhG5vO4IGSM.PIbK3Ve9IvpfoicMwXbFEXQFi', 'active', 'air_force', NULL, NULL),
('tester4', '2025-12-01', 'tester', 'testing', NULL, 'Fredericksburg', 'VA', NULL, '5405405405', 0, '', '', '', '', 'tester@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$nILE/qxdpSvIgROc1uQEV.MyflEdG0IuNLQQ1c1u54MSEYKlg2LC2', 'Active duty', 'Space Force', NULL, ''),
('testing123', '2025-10-26', 'Test', 'User', NULL, 'City', 'VA', NULL, '', 0, NULL, NULL, NULL, NULL, 'example@email.com', 'true', NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, '$2y$10$XbXkJUMSAGo9m1/GZQ3faebtJWbPMZYm/AeTA3jpDCaxZBNnMclxC', 'civ', 'marine_corp', NULL, NULL),
('toaster', '2025-12-08', 'toast', 'er', NULL, 'Fredericksburg', 'VA', NULL, '5405405405', 0, '', '', '', '', 'toaster@gmail.com', 'false', '', '', '', '', '', '', '', '$2y$10$VzLJcSjn/WFh0jeI9iFAw.McczukN4ovZuzg9vgtKFlXL3i/O9oOq', 'Civilian', 'Navy', NULL, ''),
('vmsroot', NULL, 'vmsroot', '', 'N/A', 'N/A', 'VA', 'N/A', '', 0, 'N/A', 'N/A', 'N/A', NULL, '', NULL, 'vmsroot', 'N/A', 'N/A', 'email', 'superadmin', 'Active', 'System root user account', '$2y$10$.3p8xvmUqmxNztEzMJQRBesLDwdiRU3xnt/HOcJtsglwsbUk88VTO', NULL, NULL, 0, 'vmsroot'),
('Volunteer25', '2025-04-30', 'Volley', 'McTear', '123 Dog St', 'Dogville', 'VA', '56748', '9887765543', 0, 'home', '6565651122', 'home', '2025-04-29', 'volly@gmail.com', NULL, 'Holly', 'n/a', 'Besty', NULL, 'volunteer', 'Active', NULL, '$2y$10$45gKdbjW78pNKX/5ROtb7eU9OykSCsP/QCyTAvqBtord4J7V3Ywga', NULL, NULL, 0, 'McTear'),
('Welp', '2025-12-04', 'Jake', 'Lipinski', NULL, 'Apple', 'VA', NULL, '7577903325', 0, '', '', '', '', 'mcdonalds@happymeal.com', 'true', '', '', '', '', '', '', '', '$2y$10$LvWD62DJ6pwlVGnWenQkneWCFINzgbHgzyvaBdiLn72/WwM4wo7Iy', 'Active duty', 'Air Force', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `dbreturns`
--

CREATE TABLE `dbreturns` (
  `return_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `checkout_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbstats`
--

CREATE TABLE `dbstats` (
  `material_id` int(11) NOT NULL,
  `times_checkedout` int(11) NOT NULL DEFAULT 0,
  `last_checkout` date DEFAULT NULL,
  `last_return` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dbstats`
--

INSERT INTO `dbstats` (`material_id`, `times_checkedout`, `last_checkout`, `last_return`) VALUES
(1, 2, '2026-04-12', '2026-04-12'),
(2, 0, NULL, NULL),
(3, 0, NULL, NULL),
(4, 0, NULL, NULL),
(5, 2, '2026-04-15', '2026-04-15'),
(6, 1, '2026-04-20', '2026-04-20'),
(7, 0, NULL, NULL),
(20, 0, NULL, NULL),
(21, 0, NULL, NULL),
(22, 0, NULL, NULL),
(23, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dbcheckout`
--
ALTER TABLE `dbcheckout`
  ADD PRIMARY KEY (`checkout_id`);

--
-- Indexes for table `dblogs`
--
ALTER TABLE `dblogs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `dbmaterials`
--
ALTER TABLE `dbmaterials`
  ADD PRIMARY KEY (`material_id`);

--
-- Indexes for table `dbpersons`
--
ALTER TABLE `dbpersons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbreturns`
--
ALTER TABLE `dbreturns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `dbstats`
--
ALTER TABLE `dbstats`
  ADD PRIMARY KEY (`material_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dbcheckout`
--
ALTER TABLE `dbcheckout`
  MODIFY `checkout_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `dblogs`
--
ALTER TABLE `dblogs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `dbmaterials`
--
ALTER TABLE `dbmaterials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `dbreturns`
--
ALTER TABLE `dbreturns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
