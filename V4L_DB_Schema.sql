/*
DROP TABLE IF EXISTS Content;
DROP TABLE IF EXISTS ContentSeries;
DROP TABLE IF EXISTS ContentSeriesContent;
DROP TABLE IF EXISTS ContentTag;
DROP TABLE IF EXISTS ContentTagContent;
DROP TABLE IF EXISTS LocallyUniqueID;
DROP TABLE IF EXISTS Logon;
DROP TABLE IF EXISTS Rating;
DROP TABLE IF EXISTS User;
DROP FUNCTION IF EXISTS fnGetLUID;
 */


CREATE TABLE v4l.LocallyUniqueID
(
	biLocallyUniqueID BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL,
	vchUsedAs VARCHAR(128) NOT NULL COMMENT 'Tag indicating where this ID is primarily used.',
	tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	CONSTRAINT LocallyUniqueID_biLocallyUniqueID_uindex
		UNIQUE (biLocallyUniqueID)
)
COMMENT 'To avoid the overhead of UUIDs, we will serve IDs and maintain uniqueness within the system.'
;


CREATE FUNCTION v4l.fnGetLUID (vchUse VARCHAR(128)) RETURNS BIGINT
BEGIN
	-- ------------------------------------------------------
	-- Generates, logs, and returns a new locally unique ID.
	--
	-- History:
	--  2017-06-26 J. Hawkins: Initial Version
	-- ------------------------------------------------------
	INSERT INTO LocallyUniqueID (vchUsedAs) VALUES (vchUse);

  -- Use of LAST_INSERT_ID() should be safe against race conditions
  --  due to context... Worth testing.
	RETURN LAST_INSERT_ID();
END;


CREATE TABLE v4l.Logon
(
	biLogonID BIGINT DEFAULT 1 PRIMARY KEY NOT NULL,
	biUserID BIGINT NOT NULL,
	iIPAddress INT NOT NULL,
	tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);
CREATE TRIGGER beforeInsertLogon
  BEFORE INSERT ON Logon
  FOR EACH ROW
  SET new.biLogonID = fnGetLUID('biLogonID');
-- CREATE UNIQUE INDEX Logon_biLogonID_uindex ON v4l.Logon (biLogonID);


CREATE TABLE v4l.Content
(
	biContentID BIGINT DEFAULT 1 PRIMARY KEY NOT NULL,
  siContentType SMALLINT DEFAULT 0 NOT NULL,
  vchContentName VARCHAR(256) NOT NULL,
  txtContentDescription TEXT,
  txtContentText TEXT,
  txtContentURL TEXT,
  biUserID BIGINT NOT NULL,
	tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);
-- CREATE UNIQUE INDEX Logon_biContentID_uindex ON v4l.Content (biContentID);
-- CREATE UNIQUE INDEX Logon_vchContentName_uindex ON v4l.Content (vchContentName);
-- CREATE UNIQUE INDEX Logon_txtContentURL_uindex ON v4l.Content (txtContentURL);
CREATE TRIGGER beforeInsertContent
  BEFORE INSERT ON Content
  FOR EACH ROW
  SET new.biContentID = fnGetLUID('biContentID');


CREATE TABLE v4l.ContentSeries
(
  biContentSeriesID BIGINT DEFAULT 1 PRIMARY KEY NOT NULL,
  vchContentSeriesName VARCHAR(256) NOT NULL,
  txtContentSeriesDescription TEXT,
	tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TRIGGER beforeInsertContentSeries
  BEFORE INSERT ON ContentSeries
  FOR EACH ROW
  SET new.biContentSeriesID = fnGetLUID('biContentSeriesID');
-- CREATE UNIQUE INDEX contentseries_uucontentseriesid_uindex ON contentseries (uucontentseriesid);
-- CREATE UNIQUE INDEX contentseries_vchcontentseriesname_uindex ON contentseries (vchcontentseriesname);


CREATE TABLE v4l.ContentSeriesContent
(
    biContentSeriesID BIGINT NOT NULL,
    biContentID BIGINT NOT NULL,
    tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ContentSeriesContent_biContentSeriesID_biContentID_pk PRIMARY KEY (biContentSeriesID, biContentID)
);


create table v4l.ContentTag
(
  biContentTagID bigint default '1' not null
    primary key,
  vchContentTag varchar(128) not null,
  txtContentTagDescription text null,
  tsCreate timestamp default CURRENT_TIMESTAMP not null,
  tsUpdate timestamp default CURRENT_TIMESTAMP not null,
  constraint ContentTag_vchContentTag_uindex
  unique (vchContentTag)
)
;
CREATE TRIGGER v4l.beforeInsertContentTag
BEFORE INSERT ON v4l.ContentTag
FOR EACH ROW
  SET new.biContentTagID = fnGetLUID('biContentTagID');
-- CREATE UNIQUE INDEX contenttag_uucontenttagid_uindex ON contenttag (uucontenttagid);
-- CREATE UNIQUE INDEX contenttag_vchcontenttag_uindex ON contenttag (vchcontenttag);


CREATE TABLE ContentTagContent
(
  biContentTagContentID BIGINT DEFAULT 1 PRIMARY KEY NOT NULL,
  biContentTagID BIGINT NOT NULL,
  biContentID BIGINT NOT NULL,
  tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);
CREATE TRIGGER beforeInsertContentTagContent
  BEFORE INSERT ON ContentTagContent
  FOR EACH ROW
  SET new.biContentTagContentID = fnGetLUID('biContentTagContentID');
-- CREATE UNIQUE INDEX contenttagcontent_uucontenttagcontentid_uindex ON contenttagcontent (uucontenttagcontentid);


CREATE TABLE Rating
(
  biRatingID BIGINT DEFAULT 1 PRIMARY KEY NOT NULL,
  biRaterID BIGINT NOT NULL,
  biRatedID BIGINT NOT NULL,
  siRatingStars SMALLINT DEFAULT 0 NOT NULL,
  vchRatingTitle VARCHAR(256),
  txtRatingText TEXT,
  tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);
CREATE TRIGGER beforeInsertRating
  BEFORE INSERT ON Rating
  FOR EACH ROW
  SET new.biRatingID = fnGetLUID('biRatingID');
-- CREATE UNIQUE INDEX "rating_uuRatedID_uuRaterID_pk" ON rating (uuratedid, uuraterid);


CREATE TABLE User
(
  biUserID BIGINT DEFAULT 1 PRIMARY KEY NOT NULL,
  vchUserName VARCHAR(256) NOT NULL,
  vchNameFirst VARCHAR(64) DEFAULT NULL,
  vchNameLast VARCHAR(64) DEFAULT NULL,
  dtBirthday DATE,
  dtLicenseAccepted DATE,
  vchContacteMail VARCHAR(256) DEFAULT NULL,
  siReputation SMALLINT DEFAULT 10 NOT NULL,
  siStatus BOOLEAN DEFAULT true NOT NULL,
  tsCreate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	tsUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);
CREATE TRIGGER beforeInsertUser
  BEFORE INSERT ON User
  FOR EACH ROW
  SET new.biUserID = fnGetLUID('biUserID');
-- CREATE UNIQUE INDEX users_uuuserid_uindex ON users (uuuserid);
-- CREATE UNIQUE INDEX users_vchusername_uindex ON users (vchusername);
-- CREATE FUNCTION addcontenttag(tag VARCHAR, tagdesc TEXT) RETURNS REFCURSOR;
















