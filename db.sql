-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE =
        'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema attendance
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `attendance` DEFAULT CHARACTER SET utf8;
USE `attendance`;

-- -----------------------------------------------------
-- Table `attendance`.`employees`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`employees`
(
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255)    NOT NULL,
    `email`       VARCHAR(255)    NOT NULL,
    `password`    VARCHAR(255)    NOT NULL,
    `admin`       INT             NOT NULL DEFAULT 0,
    `description` TEXT            NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attendance`.`departments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`departments`
(
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `manager_id`  BIGINT UNSIGNED NOT NULL,
    `name`        VARCHAR(255)    NOT NULL,
    `description` TEXT            NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_departments_employees1_idx` (`manager_id` ASC) VISIBLE,
    CONSTRAINT `fk_departments_employees1`
        FOREIGN KEY (`manager_id`)
            REFERENCES `attendance`.`employees` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attendance`.`assignments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`assignments`
(
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `employee_id`   BIGINT UNSIGNED NOT NULL,
    `start_date`    DATE            NOT NULL,
    `end_date`      DATE            NULL,
    `description`   TEXT            NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_assignments_employees_idx` (`employee_id` ASC) VISIBLE,
    INDEX `fk_assignments_departments1_idx` (`department_id` ASC) VISIBLE,
    CONSTRAINT `fk_assignments_employees`
        FOREIGN KEY (`employee_id`)
            REFERENCES `attendance`.`employees` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk_assignments_departments1`
        FOREIGN KEY (`department_id`)
            REFERENCES `attendance`.`departments` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attendance`.`statuses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`statuses`
(
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `name`          VARCHAR(255)    NOT NULL,
    `description`   TEXT            NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_statuses_departmens1_idx` (`department_id` ASC) VISIBLE,
    CONSTRAINT `fk_statuses_departments1`
        FOREIGN KEY (`department_id`)
            REFERENCES `attendance`.`departments` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attendance`.`attendances`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`attendances`
(
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` BIGINT UNSIGNED NOT NULL,
    `status_id`     INT UNSIGNED    NOT NULL,
    `date`          DATE            NOT NULL,
    `description`   TEXT            NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_attendance_assignments1_idx` (`assignment_id` ASC) VISIBLE,
    INDEX `fk_attendance_statuses1_idx` (`status_id` ASC) VISIBLE,
    CONSTRAINT `fk_attendance_assignments1`
        FOREIGN KEY (`assignment_id`)
            REFERENCES `attendance`.`assignments` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_statuses1`
        FOREIGN KEY (`status_id`)
            REFERENCES `attendance`.`statuses` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attendance`.`holidays`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`holidays`
(
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `name`          VARCHAR(255)    NOT NULL,
    `date`          DATE            NOT NULL,
    `description`   TEXT            NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_holidays_departments1_idx` (`department_id` ASC) VISIBLE,
    CONSTRAINT `fk_holidays_departments1`
        FOREIGN KEY (`department_id`)
            REFERENCES `attendance`.`departments` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;

USE `attendance`;

-- -----------------------------------------------------
-- Placeholder table for view `attendance`.`assignments_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`assignments_view`
(
    `id`                INT,
    `department_id`     INT,
    `employee_id`       INT,
    `start_date`        INT,
    `end_date`          INT,
    `description`       INT,
    `'department_name'` INT,
    `'employee_name'`   INT,
    `'employee_email'`  INT
);

-- -----------------------------------------------------
-- Placeholder table for view `attendance`.`departments_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`departments_view`
(
    `id`              INT,
    `manager_id`      INT,
    `name`            INT,
    `description`     INT,
    `'manager_name'`  INT,
    `'manager_email'` INT
);

-- -----------------------------------------------------
-- Placeholder table for view `attendance`.`attendances_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`attendances_view`
(
    `id`            INT,
    `assignment_id` INT,
    `status_id`     INT,
    `date`          INT,
    `description`   INT,
    `'status'`      INT
);

-- -----------------------------------------------------
-- Placeholder table for view `attendance`.`reports_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance`.`reports_view`
(
    `department_id`  INT,
    `employee_id`    INT,
    `employee_name`  INT,
    `employee_email` INT,
    `date`           INT,
    `status`         INT
);

-- -----------------------------------------------------
-- View `attendance`.`assignments_view`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendance`.`assignments_view`;
USE `attendance`;
CREATE OR REPLACE VIEW `assignments_view` AS
SELECT a.*, d.name AS 'department_name', e.name AS 'employee_name', e.email AS 'employee_email'
FROM assignments a
         INNER JOIN departments d ON d.id = a.department_id
         INNER JOIN employees e ON e.id = a.employee_id;

-- -----------------------------------------------------
-- View `attendance`.`departments_view`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendance`.`departments_view`;
USE `attendance`;
CREATE OR REPLACE VIEW `departments_view` AS
SELECT d.*, e.name AS 'manager_name', e.email AS 'manager_email'
FROM departments d
         INNER JOIN employees e ON e.id = d.manager_id;

-- -----------------------------------------------------
-- View `attendance`.`attendances_view`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendance`.`attendances_view`;
USE `attendance`;
CREATE OR REPLACE VIEW `attendances_view` AS
SELECT a.*, s.name AS 'status'
FROM attendances a
         INNER JOIN statuses s ON s.id = a.status_id;

-- -----------------------------------------------------
-- View `attendance`.`reports_view`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendance`.`reports_view`;
USE `attendance`;
CREATE OR REPLACE VIEW `reports_view` AS
SELECT asg.department_id, asg.employee_id, asg.employee_name, asg.employee_email, atd.date, atd.status
FROM attendances_view atd
         INNER JOIN assignments_view asg ON asg.id = atd.assignment_id
ORDER BY asg.employee_name, atd.date;

SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
