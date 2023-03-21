-- CREATE TABLE accounts (
--     id INTEGER PRIMARY KEY AUTOINCREMENT,
--     name TEXT NOT NULL,
--     surname TEXT NOT NULL,
--     email TEXT NOT NULL,
--     password TEXT NOT NULL
-- )
-- ALTER TABLE accounts ADD confirmationKey TEXT NOT NULL
-- ALTER TABLE accounts ADD confirmedAccount INT NOT NULL

-- CREATE TABLE books (
--     id INTEGER PRIMARY KEY AUTOINCREMENT,
--     title TEXT NOT NULL,
--     author TEXT NOT NULL,
--     translator TEXT,
--     collection TEXT,
--     edition TEXT,
--     publication DATE,
--     pages INTEGER
-- )
-- ALTER TABLE books ADD email TEXT NOT NULL
-- ALTER TABLE books ADD available INTEGER NOT NULL

-- CREATE TABLE recupCode (
--     id INTEGER PRIMARY KEY AUTOINCREMENT,
--     email TEXT NOT NULL,
--     code INTEGER NOT NULL
-- )

-- CREATE TABLE borrowed_books (
--     id INTEGER PRIMARY KEY AUTOINCREMENT,
--     date_borrowed TEXT NOT NULL,
--     date_return TEXT NOT NULL,
--     extension INTEGER NOT NULL,
--     book_id INTEGER NOT NULL,
--     email TEXT NOT NULL
-- )

UPDATE books SET available = 1 WHERE id = 24