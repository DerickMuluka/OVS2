CREATE DATABASE ovs_system CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ovs_system;

-- ─────────────────────────────────────────────
-- ADMINS
-- ─────────────────────────────────────────────
CREATE TABLE admin (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- VOTERS
-- ─────────────────────────────────────────────
CREATE TABLE voter (
    voter_id      INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) UNIQUE NOT NULL,
    password      VARCHAR(255) NOT NULL,
    phone         VARCHAR(15)  UNIQUE,
    dob           DATE NOT NULL,
    voter_card_no VARCHAR(30)  UNIQUE NOT NULL,
    is_verified   BOOLEAN DEFAULT FALSE,
    has_voted     BOOLEAN DEFAULT FALSE,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- ELECTIONS
-- ─────────────────────────────────────────────
CREATE TABLE election (
    election_id INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    start_date  DATETIME NOT NULL,
    end_date    DATETIME NOT NULL,
    status      ENUM('upcoming','active','closed') DEFAULT 'upcoming',
    created_by  INT,
    CONSTRAINT fk_election_admin
        FOREIGN KEY (created_by) REFERENCES admin(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- CANDIDATES
-- ─────────────────────────────────────────────
CREATE TABLE candidate (
    candidate_id INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    party        VARCHAR(100),
    manifesto    TEXT,
    photo_url    VARCHAR(255),
    election_id  INT,
    CONSTRAINT fk_candidate_election
        FOREIGN KEY (election_id) REFERENCES election(election_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- VOTES
-- ─────────────────────────────────────────────
CREATE TABLE vote (
    vote_id      INT AUTO_INCREMENT PRIMARY KEY,
    voter_id     INT NOT NULL,
    candidate_id INT NOT NULL,
    election_id  INT NOT NULL,
    voted_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_voter     FOREIGN KEY (voter_id)     REFERENCES voter(voter_id)         ON DELETE CASCADE,
    CONSTRAINT fk_vote_candidate FOREIGN KEY (candidate_id) REFERENCES candidate(candidate_id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_election  FOREIGN KEY (election_id)  REFERENCES election(election_id)   ON DELETE CASCADE,
    CONSTRAINT uq_voter_election UNIQUE (voter_id, election_id)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- VIEWS for reporting
-- ─────────────────────────────────────────────
CREATE VIEW v_candidate_totals AS
SELECT e.election_id, e.title AS election_title,
       c.candidate_id, c.name AS candidate_name, c.party,
       COUNT(v.vote_id) AS total_votes
FROM election e
JOIN candidate c ON c.election_id = e.election_id
LEFT JOIN vote v ON v.candidate_id = c.candidate_id AND v.election_id = e.election_id
GROUP BY e.election_id, e.title, c.candidate_id, c.name, c.party;

CREATE OR REPLACE VIEW v_election_turnout AS
SELECT e.election_id, e.title, e.status,
       (SELECT COUNT(*) FROM voter WHERE is_verified = TRUE) AS registered_voters,
       (SELECT COUNT(*) FROM vote WHERE election_id = e.election_id) AS votes_cast
FROM election e;
SELECT * FROM v_candidate_totals;

-- ─────────────────────────────────────────────
-- SAMPLE DATA
-- ─────────────────────────────────────────────
-- Admin password below = "admin123"
INSERT INTO admin (username, password, email) VALUES
('superadmin', '$2y$10$3zP7D8lKfHq2QvJXGnUOZejK3cHv1RXu7tQ6t7sJfWvX4bQ9sNlIe', 'admin@vote.com');

-- Voter password below = "voter123"
INSERT INTO voter (full_name, email, password, phone, dob, voter_card_no, is_verified, has_voted) VALUES
('John Kamau',   'john@mail.com',  '$2y$10$4cQ8E9mMhIr3RwKYHoVPafL4dIw2SYv8uR7u8tKgXwY5cR0tOmJf', '0711000001', '1990-05-10', 'VTR001', TRUE,  TRUE),
('Mary Wanjiku', 'mary@mail.com',  '$2y$10$4cQ8E9mMhIr3RwKYHoVPafL4dIw2SYv8uR7u8tKgXwY5cR0tOmJf', '0711000002', '1992-08-15', 'VTR002', TRUE,  TRUE),
('Peter Otieno', 'peter@mail.com', '$2y$10$4cQ8E9mMhIr3RwKYHoVPafL4dIw2SYv8uR7u8tKgXwY5cR0tOmJf', '0711000003', '1988-02-20', 'VTR003', TRUE,  TRUE),
('Grace Achieng','grace@mail.com', '$2y$10$4cQ8E9mMhIr3RwKYHoVPafL4dIw2SYv8uR7u8tKgXwY5cR0tOmJf', '0711000004', '1995-11-01', 'VTR004', TRUE,  TRUE),
('David Mwangi', 'david@mail.com', '$2y$10$4cQ8E9mMhIr3RwKYHoVPafL4dIw2SYv8uR7u8tKgXwY5cR0tOmJf', '0711000005', '1993-07-25', 'VTR005', TRUE,  FALSE),
('Sarah Njeri',  'sarah@mail.com', '$2y$10$4cQ8E9mMhIr3RwKYHoVPafL4dIw2SYv8uR7u8tKgXwY5cR0tOmJf', '0711000006', '1991-09-09', 'VTR006', FALSE, FALSE);

INSERT INTO election (title, description, start_date, end_date, status, created_by) VALUES
('President 2025', 'National Presidential Election',
 '2025-01-01 08:00:00','2025-01-31 18:00:00','active', 1),
('Governor Nairobi 2025','Nairobi County Gubernatorial Election',
 '2025-02-01 08:00:00','2025-02-28 18:00:00','upcoming', 1);

INSERT INTO candidate (name, party, manifesto, election_id) VALUES
('Alice Wanjiru','Party A','Better education for all',1),
('Bob Kiprop',   'Party B','Economic growth and jobs',1),
('Carol Muthoni','Party C','Universal healthcare',1),
('Dennis Ouma',  'Party D','Youth empowerment',1),
('Eva Njoroge',  'Party X','Clean water for Nairobi',2),
('Frank Mutua',  'Party Y','Better roads and transport',2);

INSERT INTO vote (voter_id, candidate_id, election_id) VALUES
(1, 2, 1),
(2, 1, 1),
(3, 1, 1),
(4, 3, 1);