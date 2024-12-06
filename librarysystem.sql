-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2024 at 07:33 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `librarysystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `BookID` int(11) NOT NULL,
  `ResourceID` int(11) NOT NULL,
  `Author` varchar(255) DEFAULT NULL,
  `ISBN` varchar(13) DEFAULT NULL,
  `Publisher` varchar(255) DEFAULT NULL,
  `Edition` varchar(50) DEFAULT NULL,
  `PublicationDate` date DEFAULT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `AvailableQuantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`BookID`, `ResourceID`, `Author`, `ISBN`, `Publisher`, `Edition`, `PublicationDate`, `Quantity`, `AvailableQuantity`) VALUES
(14, 7, 'Ako', '445', 'Yatap', '4th Edition (2033)', '2025-01-08', 2, 2),
(15, 9, 'Aljun', '2211', 'Ikaw', '5th Edition (2023)', '2024-12-03', 1, 1),
(16, 1, 'Ikaw', '112', 'Patay', '4th Edition (2033)', '2024-12-03', 2, 2),
(17, 1, 'Aljur', '222', 'Mark Lou', '1st Edition(2012)', '2024-12-06', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `BorrowID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `BookID` int(11) NOT NULL,
  `BorrowDate` date NOT NULL,
  `DueDate` date NOT NULL,
  `Status` varchar(50) NOT NULL,
  `ReturnDate` date DEFAULT NULL,
  `Fine` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`BorrowID`, `UserID`, `BookID`, `BorrowDate`, `DueDate`, `Status`, `ReturnDate`, `Fine`) VALUES
(1, 3, 16, '2024-12-06', '2024-12-09', 'returned', '2024-12-06', '0.00'),
(2, 3, 17, '2024-12-06', '2024-12-09', 'returned', '2024-12-06', '0.00'),
(3, 3, 17, '2024-12-06', '2024-12-09', 'returned', '2024-12-06', '0.00'),
(4, 3, 15, '2024-12-06', '2024-12-09', 'returned', '2024-12-06', '0.00'),
(5, 3, 17, '2024-12-06', '2024-12-09', 'returned', '2024-12-06', '0.00'),
(6, 3, 14, '2024-12-06', '2024-12-09', 'returned', '2024-12-06', '0.00'),
(7, 3, 14, '2024-12-06', '2024-12-09', 'pending', NULL, '0.00'),
(8, 3, 14, '2024-12-06', '2024-12-09', 'pending', NULL, '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `libraryresources`
--

CREATE TABLE `libraryresources` (
  `ResourceID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `AccessionNumber` varchar(50) NOT NULL,
  `Category` enum('Book','Periodical','Media') NOT NULL,
  `Quantity` int(11) DEFAULT 1,
  `AvailableQuantity` int(11) DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `libraryresources`
--

INSERT INTO `libraryresources` (`ResourceID`, `Title`, `AccessionNumber`, `Category`, `Quantity`, `AvailableQuantity`, `CreatedAt`) VALUES
(1, 'DUGAGO', 'P-2024-001', 'Periodical', 1, 1, '2024-12-02 13:31:18'),
(7, 'MAGPAKYU KA', 'B-2024-001', 'Book', 2, 2, '2024-12-02 13:38:18'),
(8, 'KUPAL KA BA BOSS?', 'R-2024-001', 'Media', 1, 1, '2024-12-02 13:38:29'),
(9, 'KUPAL KA BA BOSS?', 'R-2024-002', 'Media', 1, 1, '2024-12-02 13:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `mediaresources`
--

CREATE TABLE `mediaresources` (
  `MediaID` int(11) NOT NULL,
  `ResourceID` int(11) NOT NULL,
  `Format` varchar(50) DEFAULT NULL,
  `Runtime` varchar(50) DEFAULT NULL,
  `MediaType` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `periodicals`
--

CREATE TABLE `periodicals` (
  `PeriodicalID` int(11) NOT NULL,
  `ResourceID` int(11) NOT NULL,
  `ISSN` varchar(20) DEFAULT NULL,
  `Volume` varchar(20) DEFAULT NULL,
  `Issue` varchar(20) DEFAULT NULL,
  `PublicationDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `TransactionID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ResourceID` int(11) NOT NULL,
  `BorrowDate` datetime NOT NULL,
  `DueDate` datetime NOT NULL,
  `ReturnDate` datetime DEFAULT NULL,
  `Fine` decimal(10,2) DEFAULT 0.00,
  `Status` enum('Borrowed','Returned') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `UserType` enum('student','faculty','admin','staff') NOT NULL,
  `MembershipID` varchar(50) NOT NULL,
  `ContactDetails` text DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `BorrowingLimit` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Name`, `UserType`, `MembershipID`, `ContactDetails`, `Password`, `BorrowingLimit`) VALUES
(1, 'Royce Fernandez', 'admin', '1', '09866566454', '$2y$10$tbnwms.dwy36ZpzAcJEb.OgV4EY.Mn32Yhvqif/xKWVktWHmSpate', 3),
(3, 'Bayot', 'faculty', '2', '09656478778', '$2y$10$sqlhHuIkx4IcVaCGBrmfD.HAkUefP7cRD9oY32iUjt4ygV1M86BnS', 5),
(5, 'Bayot', 'staff', '3', '09656478778', '$2y$10$ozT/S9dsaT8VhRf9DpsveemI5ceKkTGX.unQJL4fYLWmMnRvIo1he', 5),
(6, 'Gago', 'student', '4', '09787745445', '$2y$10$qXW5TCBsBqoAM6NWsm3uS.A/zz7TA/fNyWw/N0WFjaUN0uexIjccy', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`BookID`),
  ADD KEY `ResourceID` (`ResourceID`);

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`BorrowID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `BookID` (`BookID`);

--
-- Indexes for table `libraryresources`
--
ALTER TABLE `libraryresources`
  ADD PRIMARY KEY (`ResourceID`),
  ADD UNIQUE KEY `AccessionNumber` (`AccessionNumber`);

--
-- Indexes for table `mediaresources`
--
ALTER TABLE `mediaresources`
  ADD PRIMARY KEY (`MediaID`),
  ADD KEY `ResourceID` (`ResourceID`);

--
-- Indexes for table `periodicals`
--
ALTER TABLE `periodicals`
  ADD PRIMARY KEY (`PeriodicalID`),
  ADD KEY `ResourceID` (`ResourceID`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `ResourceID` (`ResourceID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `MembershipID` (`MembershipID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `BookID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `BorrowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `libraryresources`
--
ALTER TABLE `libraryresources`
  MODIFY `ResourceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mediaresources`
--
ALTER TABLE `mediaresources`
  MODIFY `MediaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `periodicals`
--
ALTER TABLE `periodicals`
  MODIFY `PeriodicalID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`ResourceID`) REFERENCES `libraryresources` (`ResourceID`) ON DELETE CASCADE;

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`);

--
-- Constraints for table `mediaresources`
--
ALTER TABLE `mediaresources`
  ADD CONSTRAINT `mediaresources_ibfk_1` FOREIGN KEY (`ResourceID`) REFERENCES `libraryresources` (`ResourceID`) ON DELETE CASCADE;

--
-- Constraints for table `periodicals`
--
ALTER TABLE `periodicals`
  ADD CONSTRAINT `periodicals_ibfk_1` FOREIGN KEY (`ResourceID`) REFERENCES `libraryresources` (`ResourceID`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`ResourceID`) REFERENCES `libraryresources` (`ResourceID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
