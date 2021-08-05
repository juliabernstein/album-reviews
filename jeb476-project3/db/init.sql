-- USERS TABLE --

CREATE TABLE users (
	id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    name TEXT NOT NULL,
	username	TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);

INSERT INTO users (id, name, username, password) VALUES (1, 'Sofi Andrade', 'sofi', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (2, 'Judith Peraino', 'peraino', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (3, 'Julia Bernstein', 'julia', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (4, 'Zaira Paredes', 'zaira', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (5, 'Maddie Sand', 'maddie', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (6, 'Madelyn Yu', 'madelyn', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey
INSERT INTO users (id, name, username, password) VALUES (7, 'Jasmine Herrera', 'jasmine', '$2y$10$QtCybkpkzh7x5VN11APHned4J8fu78.eFXlyAMmahuAaNcbwZ7FH.'); -- password: monkey

-- ALBUM REVIEWS TABLE --

CREATE TABLE albums (
    id         INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    user_id    TEXT NOT NULL,
    album      TEXT NOT NULL,
    artist     TEXT NOT NULL,
    year       TEXT NOT NULL,
    name       TEXT NOT NULL,
    rating     INTEGER NOT NULL,
    review      TEXT,
    filename    TEXT NOT NULL,
    file_ext    TEXT NOT NULL,
    source      TEXT,

    FOREIGN KEY(user_id) REFERENCES users(id)
);

--- Sessions ---

CREATE TABLE sessions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
	session TEXT NOT NULL UNIQUE,
    last_login   TEXT NOT NULL,

  FOREIGN KEY(user_id) REFERENCES users(id)
);

--- Groups ----

CREATE TABLE groups (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL UNIQUE
);

INSERT INTO groups (id, name) VALUES (1, 'admin');
INSERT INTO groups (id, name) VALUES (2, 'user');

--- Group Membership

CREATE TABLE memberships (
  id        INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
  group_id  INTEGER NOT NULL,
  user_id   INTEGER NOT NULL,

  FOREIGN KEY(group_id) REFERENCES groups(id),
  FOREIGN KEY(user_id) REFERENCES users(id)
);

INSERT INTO memberships (id, group_id, user_id) VALUES (1, 2, 1); -- User 'sofi' is a member of the 'users' group.
INSERT INTO memberships (id, group_id, user_id) VALUES (2, 1, 2); -- User 'peraino' is a member of the 'admin' group.
INSERT INTO memberships (id, group_id, user_id) VALUES (3, 1, 3); -- User 'julia' is a member of the 'admin' group.
INSERT INTO memberships (id, group_id, user_id) VALUES (4, 2, 4); -- User 'zaira' is a member of the 'users' group.
INSERT INTO memberships (id, group_id, user_id) VALUES (5, 2, 5); -- User 'maddie' is a member of the 'users' group.
INSERT INTO memberships (id, group_id, user_id) VALUES (6, 2, 6); -- User 'madelyn' is a member of the 'users' group.
INSERT INTO memberships (id, group_id, user_id) VALUES (7, 2, 7); -- User 'jasmine' is a member of the 'users' group.



-- SEED DATA --

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (1, 3, 'The New Abnormal', 'The Strokes', '2020', 'Julia Bernstein', 9, '"The New Abnormal" is an excellent album by The Strokes. One of my favorites, actually. The only reason I gave it a 9 was because "Bad Decisions" sounds exactly like Billy Idol''s "Dancing With Myself," but overall I''m really impressed by the material on this album. Grammy was well deserved.', 'new-abnormal.jpg', 'jpg', 'https://fordhamobserver.com/45592/arts-and-culture/ram-jams-the-new-abnormal-is-new-but-not-abnormal/');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (2, 5, 'Pure Heroine', 'Lorde', '2013', 'Maddie Sand', 10, 'Lorde’s Pure Heroine is an amazing album that dissects modern childhood and loss of innocence. The structure, sound, and story told through the music is one that deeply resonated with my own fear of growing up and is seeped in nostalgia!', 'pure-heroine.jpg', 'jpg', 'https://www.discogs.com/Lorde-Pure-Heroine/release/4951590');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (3, 4, 'A Rush of Blood to the Head', 'Coldplay', '2002', 'Zaira Paredes', 10, 'Best album that Coldplay has produced, it perfectly blends a multitude of emotions. From angst drum pounding to divine guitar riffs, Rush of Blood to The Head has a lot to offer to anyone at any point in their lives', 'rush-of-blood.jpg', 'jpg', 'https://en.wikipedia.org/wiki/A_Rush_of_Blood_to_the_Head');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (4, 1, 'Making a Door Less Open', 'Car Seat Headrest', '2020', 'Sofi Andrade', 9.5, 'Car Seat Headrest’s newest album, “Making a Door Less Open,” is a sweeping blend of genres, all characterized by the same relentless dynamism. Rock ‘n’ roll tradition and techno influence compete throughout the album, rising and crashing into each other in every song.', 'door-less-open.jpg', 'jpg', 'https://carseatheadrest.bandcamp.com/album/making-a-door-less-open');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (5, 1, 'Fake It Flowers', 'Beabadoobee', '2020', 'Sofi Andrade', 9, 'On her newest release and debut album, “Fake It Flowers,” Beabadoobee parts with that acoustic guitar and other conventions of bedroom pop to forge a powerful kaleidoscope of influences, drawing from 2000s pop rock, 90s alternative and her own past repertoire. With such contrast as the raging guitar solos in “Sorry” immediately preceding the glittering synthscapes in “Further Away,” Beabadoobee continues to prove herself as a dynamic and versatile artist capable of evoking angst just as easily as heartache.', 'fake-it-flowers.jpg', 'jpg', 'https://www.amazon.com/Official-Beabadoobee-Flowers-Album-Poster/dp/B08LMD1Y2D');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (6, 3, 'In Rainbows', 'Radiohead', '2007', 'Julia Bernstein', 10, 'Easily my favorite, and in my opinion most underrated, Radiohead album. The entire album is full of beautiful, melodic, emotional songs. The album is perfectly curated with heavy contrast to keep your interest, and I think it has some of Thom Yorke''s most genius songwriting.', 'in-rainbows.png', 'png', 'https://en.wikipedia.org/wiki/In_Rainbows');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (7, 3, 'Plastic Hearts', 'Miley Cyrus', '2020', 'Julia Bernstein', 8.5, 'Miley’s message of growth in “Plastic Hearts” is loud and clear, and her image is refreshingly authentic, with the album striking a perfect balance between her confidence and vulnerability.  Miley steps confidently into a new sound-world of modern rock, with a deep, heavy, rockstar voice (which seems to have undergone a transformation from her earlier music) and a myriad of different musical influences.', 'plastic-hearts.png', 'png', 'https://en.wikipedia.org/wiki/Plastic_Hearts');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (8, 3, 'Tranquility Base Hotel & Casino', 'Arctic Monkeys', '2018', 'Julia Bernstein', 8.75, 'To be quite honest, I hated this album when I first listened to it; it felt like cheap hotel elevator jazz music, and I wanted to hear more early 2000s Arctic Monkey influences. But when I realized that that was the whole point and gave it another listen, I was obsessed. This hotel-in-outer-space themed album screams elegance, and it has absolutely no skips. My personal favorite song is "The Ultracheese."', 'tbhc.jpg', 'jpg', 'https://www.ft.com/content/4d3af848-52e1-11e8-b24e-cad6aa67e23e');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (9, 3, 'The Getaway', 'Red Hot Chili Peppers', '2016', 'Julia Bernstein', 8, 'In my opinion, a very underrated RHCP album... but definitely not their best. I think we see more of a polished version of their typical style, and more focus of melodic styles. I like it a lot, and would recommend a listen, but it''s not a great album for someone who is not familiar with their work. Start with "Blood Sugar Sex Magik" for sure.', 'the-getaway.jpg', 'jpg', 'https://www.amazon.com/Getaway-Red-Hot-Chili-Peppers/dp/B01F3REDMU');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (10, 3, 'Revolver', 'The Beatles', '1966', 'Julia Bernstein', 9.5, 'This is perhaps the most experimental Beatles album, and it''s one of my favorites. Definitely hard to listen to at first, but you have to ease into it to appreciate it. It sounds like nothing you''ve ever heard before, and their genius really comes out.', 'revolver.jpg', 'jpg', 'https://www.thebeatles.com/album/revolver');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (11, 6, 'Folklore', 'Taylor Swift', '2020', 'Madelyn Yu', 9, 'Taylor Swift branched out into yet another music genre in this album now exploring more acoustic folk styles of music. There is a wide variety of songs within this album each telling a unique story. The album of the year Grammy win was the icing on the cake of praise for this new groundbreaking album composed entirely during the pandemic.', 'folklore.png', 'png', 'https://en.wikipedia.org/wiki/Folklore_(Taylor_Swift_album)');

INSERT INTO `albums` (id, user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (12, 7, 'LP1', 'Liam Payne', '2019', 'Jasmine Hererra', 2, 'Honestly, Liam Payne is struggling to maintain relevance since the breakup of one direction and this album shows that. As he explores a club/r&b genre, he lacks a sense of identity and uniqueness in his music. AWFUL', 'lp1.jpg', 'jpg', 'https://www.amazon.com/LP1-Liam-Payne/dp/B07Z8594Q8');


-- TAGS --

CREATE TABLE tags (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    tag_name TEXT
);

INSERT INTO tags (id, tag_name) VALUES (1, "rock");
INSERT INTO tags (id, tag_name) VALUES (2, "pop");
INSERT INTO tags (id, tag_name) VALUES (3, "alt");
INSERT INTO tags (id, tag_name) VALUES (4, "indie");
INSERT INTO tags (id, tag_name) VALUES (5, "folk");



-- RELATIONSHIP BETWEEN TAGS AND REVIEWS --

CREATE TABLE album_tags (
	id          INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    album_id    TEXT NOT NULL,
    tag_id      TEXT,

    FOREIGN KEY(album_id) REFERENCES albums(id),
    FOREIGN KEY(tag_id) REFERENCES tags(id)
);

INSERT INTO album_tags (id, album_id, tag_id) VALUES (1, 1, 1);  -- new abnormal, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (2, 2, 2); -- pure heroine, pop
INSERT INTO album_tags (id, album_id, tag_id) VALUES (3, 3, 1); -- coldplay, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (4, 4, 1); -- CSH, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (5, 5, 1);  -- Beabadoobee, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (6, 6, 1); -- in rainbows, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (7, 7, 1); -- plastic hearts, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (8, 8, 1); -- tbh+c, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (9, 9, 1);  -- the getaway, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (10, 10, 1); -- revolver, rock
INSERT INTO album_tags (id, album_id, tag_id) VALUES (11, 11, 5); -- folklore, folk
INSERT INTO album_tags (id, album_id, tag_id) VALUES (12, 12, 2); -- liam payne, pop
INSERT INTO album_tags (id, album_id, tag_id) VALUES (13, 1, 3);  -- new abnormal, alt
INSERT INTO album_tags (id, album_id, tag_id) VALUES (14, 1, 4);  -- new abnormal, indie
INSERT INTO album_tags (id, album_id, tag_id) VALUES (15, 5, 4);  -- beabadoobee, indie
