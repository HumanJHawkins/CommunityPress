CREATE SCHEMA IF NOT EXISTS v4l
	DEFAULT CHARACTER SET utf8
	DEFAULT COLLATE utf8_general_ci;

USE v4l;

create table v4l.Content
(
	ContentID bigint not null default 0 primary key,
	ContentTypeID bigint not null default 0,
	ContentTitle varchar(256) not null default 'Untitled',
	ContentDescription text null,
	ContentText text null,
	ContentURL text null,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP
)
;

INSERT INTO Content (ContentID, ContentTitle, ContentDescription, ContentText, ContentURL)
VALUES (0,'Placeholder: No Title','This is a placeholder record with no actual content attached.',
				'This is a placeholder record with no actual content attached.','https://visionsforlearning.org/');

create trigger v4l.beforeInsertContent
before INSERT on v4l.Content
for each row
	SET new.ContentID = fnGetLUID('ContentID');

create table v4l.ContentType
(
	ContentTypeID bigint not null default 0 primary key,
	ContentType varchar(256) not null,
	ContentTypeDescription text null,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP
)
;

INSERT INTO ContentType (ContentTypeID, ContentType, ContentTypeDescription)
VALUES (0,'Untyped','ContentType not set.');

create trigger v4l.beforeInsertContentType
before INSERT on v4l.ContentType
for each row
	SET new.ContentTypeID = fnGetLUID('ContentTypeID');

create table v4l.ContentSeries
(
	ContentSeriesID bigint not null default 0 primary key,
	ContentSeriesTitle varchar(256) not null,
	ContentSeriesDescription text null,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP
)
;

INSERT INTO ContentSeries (ContentSeriesID, ContentSeriesTitle, ContentSeriesDescription)
VALUES (0,'Untitled','This is a placeholder record with no actual content series attached.');

create trigger v4l.beforeInsertContentSeries
before INSERT on v4l.ContentSeries
for each row
	SET new.ContentSeriesID = fnGetLUID('ContentSeriesID');

create table v4l.ContentSeriesContent
(
	ContentSeriesID bigint not null,
	ContentID bigint not null,
	IsActive boolean not null default TRUE,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	primary key (ContentSeriesID, ContentID)
)
;

create table v4l.LUID
(
	LUID bigint primary key,
	UsedFor varchar(128) not null comment 'Tag indicating where this ID is primarily used.',
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	constraint LUID_LUID_uindex
	unique (LUID)
)
	comment 'To avoid the overhead of UUIDs, we will serve IDs and maintain uniqueness within the system.'
;

INSERT INTO LUID (LUID, UsedFor)
VALUES (0,'A non-ID to indicate ID not-set.');

ALTER TABLE v4l.LUID MODIFY LUID BIGINT(20) NOT NULL AUTO_INCREMENT;

DROP TABLE IF EXISTS v4l.SessionLog;
create table v4l.SessionLog
(
	SessionLogID bigint not null default 0 primary key,
	SessionID binary(43) not null,
	IPAddress varbinary(16) not null,
	UserID bigint not null default 0,
	Status tinyint not null default 0
	comment '-2=Ended by timeout, -1=Ended by logout, 0=never logged in, 1=verified and active',
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	constraint SessionLog_SessionID_uindex
	unique (SessionID))
;

INSERT INTO SessionLog (SessionID, IPAddress)
VALUES ('0000000000000000000000000000000000000000000','0000000000000000');

create trigger v4l.beforeInsertSessionLog
before INSERT on v4l.SessionLog
for each row
	SET new.SessionLogID = fnGetLUID('SessionLogID');

create table v4l.Rating
(
	RaterID bigint not null comment 'Usually a UserID, but could be a uniquely identified algorithm, etc.',
	RatedID bigint not null comment 'The content, series, user, other rating, etc, that is being rated.',
	RatingStars tinyint default '0' not null comment 'Currently picturing -2 through +2, with 0 meaning "3 stars"',
	RatingTitle varchar(256) null,
	RatingText text null,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	primary key (RaterID, RatedID)
)
;

create table v4l.Tag
(
	TagID bigint not null default 0 primary key,
	TagCategory varchar(128) not null default 'Uncategorized' comment 'For organizing... Grades, Subjects, etc.',
	Tag varchar(128) not null,
	TagDescription text null,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	constraint Tag_Tag_uindex unique (Tag)
)
;

INSERT INTO Tag (TagID, Tag, TagDescription)
VALUES (0,'Non-tag','A placeholder tag with no meaning attached.');

create trigger v4l.beforeInsertTag
before INSERT on v4l.Tag
for each row
	SET new.TagID = fnGetLUID('TagID');

create table v4l.TagUse
(
	TagID bigint not null,
	TaggedID bigint not null comment 'ID of the user, content item, or other thing being tagged',
	IsActive boolean not null default TRUE,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP ,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	primary key (TagID, TaggedID)
)
;

drop table if exists v4l.User;
create table v4l.User
(
	UserID bigint not null default 0 primary key,
	UserName varchar(256) not null,
	PasswordSaltHash binary(60) null,
	HashLastAccess timestamp not null default CURRENT_TIMESTAMP,
	Birthday date null comment 'Primarily needed for legal or appropriateness concerns around age.',
	eMail varchar(256) null,
	Reputation smallint(6) default '10' not null,
	LicenseAcceptDate date null,
	Status tinyint(1) default '1' not null,
	CreateBy bigint not null default 0,
	CreateDate timestamp not null default CURRENT_TIMESTAMP,
	UpdateBy bigint not null default 0,
	UpdateDate timestamp not null default CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP,
	constraint User_UserName_uindex unique (UserName)
)
;

INSERT INTO User (UserName)
VALUES ('UnknownUser');

create trigger v4l.beforeInsertUser
before INSERT on v4l.User
for each row
	SET new.UserID = fnGetLUID('UserID');


create function v4l.fnGetLUID (UsedFor varchar(128)) returns bigint
	BEGIN
		-- ------------------------------------------------------
		-- Generates, logs, and returns a new locally unique ID.
		--
		-- History:
		--  2017-06-26 J. Hawkins: Initial Version
		-- ------------------------------------------------------
		INSERT INTO LUID (UsedFor) VALUES (UsedFor);

		-- Use of LAST_INSERT_ID() should be safe against race conditions
		--  due to context... Worth testing.
		RETURN LAST_INSERT_ID();
	END;

DROP FUNCTION IF EXISTS v4l.fnLogSession;
create function v4l.fnLogSession (_SessionID binary(43),_IPAddress varchar(45),_UserID bigint) returns bigint
	BEGIN
		-- ------------------------------------------------------
		-- Check if SessionLog exists, add or update if
		--   necessary.
		--
		-- Set UserID to 0 for non-users / IP only sessions
		--
		-- History:
		--  2017-07-05 J. Hawkins: Initial Version
		-- ------------------------------------------------------
		DECLARE result bigint;
		DECLARE ExistingSessionLogID bigint;
		DECLARE ExistingIPAdress binary(16);
		DECLARE ExistingUserID bigint;
		SET ExistingIPAdress = INET_ATON(_IPAddress);
		SELECT SessionLogID,UserID INTO ExistingSessionLogID,ExistingUserID FROM SessionLog WHERE SessionID = _SessionID AND IPAddress = ExistingIPAdress LIMIT 1;

		IF(ISNULL(ExistingSessionLogID) || ExistingSessionLogID = 0) THEN
			IF(ISNULL(_UserID) || _UserID = 0) THEN
				INSERT INTO SessionLog (SessionID,IPAddress) VALUES (_SessionID,ExistingIPAdress);
			ELSE
				INSERT INTO SessionLog (SessionID,IPAddress,UserID) VALUES (_SessionID,ExistingIPAdress,_UserID);
			END IF;
			SET result = LAST_INSERT_ID();
		ELSE
			IF((!ISNULL(_UserID)) && (ISNULL(ExistingUserID))) THEN
				UPDATE SessionLog SET UserID = _UserID WHERE SessionLogID = ExistingSessionLogID;
			END IF;
			SET result = ExistingSessionLogID;
		END IF;

		RETURN result;
	END;


DROP PROCEDURE IF EXISTS v4l.procGetUserByName;
create procedure v4l.procGetUserByName (theUser varchar(256))
	BEGIN
		-- Prevent high speed programmatic access. Users should not be bothered by a
		--  2 second delay on the rare cases where they correct a password typo quickly.
		DECLARE lastAccess TIMESTAMP;
		SET lastAccess = (SELECT HashLastAccess FROM User WHERE UserName = theUser);
		IF NOT ISNULL(lastAccess) THEN
			IF (SELECT TIMESTAMPDIFF(SECOND,(SELECT HashLastAccess FROM User WHERE UserName = theUser),CURRENT_TIMESTAMP)>2) THEN
				DO SLEEP(2);
			END IF;
		END IF;
		UPDATE User SET HashLastAccess = CURRENT_TIMESTAMP WHERE UserName = theUser;

		SELECT UserID,UserName,PasswordSaltHash,Birthday,eMail,Reputation,Status FROM User WHERE UserName = theUser;
	END;


DROP PROCEDURE IF EXISTS v4l.procGetUserByID;
create procedure v4l.procGetUserByID (theUserID varchar(256))
	BEGIN
		-- Prevent high speed programmatic access. Users should not be bothered by a
		--  2 second delay on the rare cases where they correct a password typo quickly.
		DECLARE lastAccess TIMESTAMP;
		SET lastAccess = (SELECT HashLastAccess FROM User WHERE UserID = theUserID);
		IF NOT ISNULL(lastAccess) THEN
			IF (SELECT TIMESTAMPDIFF(SECOND,(SELECT HashLastAccess FROM User WHERE UserID = theUserID),CURRENT_TIMESTAMP)>2) THEN
				DO SLEEP(2);
			END IF;
		END IF;
		UPDATE User SET HashLastAccess = CURRENT_TIMESTAMP WHERE UserID = theUserID;

		SELECT UserID,UserName,PasswordSaltHash,Birthday,eMail,Reputation,Status FROM User WHERE UserID = theUserID;
	END;


drop function if exists fnAddUser;
create function v4l.fnAddUser (newUser varchar(256),passHash binary(60)) returns bigint
	BEGIN
		-- ------------------------------------------------------
		-- Adds user if not already present.
		-- RETURNS:
		--   0 if already present.
		--   New user's UserID if added.
		--
		-- History:
		--  2017-07-07 J. Hawkins: Initial Version
		-- ------------------------------------------------------
		DECLARE userCount tinyint;
		SELECT COUNT(*) INTO userCount FROM User WHERE UserName = newUser;
		IF userCount > 0 THEN RETURN 0;
		ELSE
			INSERT INTO User (UserName,PasswordSaltHash) VALUES (newUser,passHash);
			RETURN (SELECT UserID FROM User WHERE UserName = newUser);
		END IF;
	END;


