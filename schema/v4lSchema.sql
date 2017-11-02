CREATE SCHEMA IF NOT EXISTS v4l
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE v4l;


DROP TABLE IF EXISTS LUID;
CREATE TABLE LUID
(
  LUID       BIGINT AUTO_INCREMENT
    PRIMARY KEY,
  usedFor    VARCHAR(128)                        NOT NULL
  COMMENT 'Tag indicating where this ID is primarily used.',
  createBy   BIGINT DEFAULT '0'                  NOT NULL,
  createTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy   BIGINT DEFAULT '0'                  NOT NULL,
  updateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT LUID_LUID_uindex
  UNIQUE (LUID)
)
  AUTO_INCREMENT = 100000
  COMMENT 'To avoid the overhead of UUIDs, we will serve IDs and maintain uniqueness within the system.';
INSERT INTO LUID (usedFor) VALUES ('Placeholder. Non-LUID');


DROP FUNCTION IF EXISTS fnGetLUID;
CREATE FUNCTION fnGetLUID(newUse VARCHAR(128))
  RETURNS BIGINT
  BEGIN
    -- ------------------------------------------------------
    -- Generates, logs, and returns a new locally unique ID.
    --
    -- History:
    --  2017-06-26 J. Hawkins: Initial Version
    -- ------------------------------------------------------
    INSERT INTO LUID (usedFor) VALUES (newUse);

    -- Use of LAST_INSERT_ID() should be safe against race conditions
    --  due to context... Worth testing.
    RETURN LAST_INSERT_ID();
  END;


DROP TABLE IF EXISTS content;
CREATE TABLE content
(
  contentID          BIGINT DEFAULT '0'                  NOT NULL PRIMARY KEY,
  contentTitle       VARCHAR(256) DEFAULT 'Untitled'     NOT NULL,
  contentSummary     TEXT                                NULL,
  contentExcerpt     TEXT                                NULL,
  contentDescription TEXT                                NULL,
  createBy           BIGINT DEFAULT '0'                  NOT NULL,
  createTime         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy           BIGINT DEFAULT '0'                  NOT NULL,
  updateTime         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

INSERT INTO content (contentID, contentTitle, contentDescription, contentExcerpt, contentSummary)
VALUES (0, 'Placeholder: No Title', 'This is a placeholder record with no actual content attached.',
        'This is a placeholder record with no actual content attached.', 'https://visionsforlearning.org/');

CREATE TRIGGER beforeInsertContent
BEFORE INSERT ON content
FOR EACH ROW
  SET new.contentID = fnGetLUID('contentID');


DROP TABLE IF EXISTS uploadFile;
CREATE TABLE uploadFile
(
  uploadFileID       BIGINT DEFAULT '0'                  NOT NULL PRIMARY KEY,
  uploadFileName     VARCHAR(256)                        NOT NULL,
  uploadFileSize     INT                                 NOT NULL,
  uploadFileMimeType VARCHAR(256)                        NOT NULL,
  uploadFilePath     TEXT                                NOT NULL,
  createBy           BIGINT DEFAULT '0'                  NOT NULL,
  createTime         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy           BIGINT DEFAULT '0'                  NOT NULL,
  updateTime         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT uploadFile_uploadFileName_uindex
  UNIQUE (uploadFileName)
);

INSERT INTO uploadFile (uploadFileID, uploadFileName, uploadFileSize, uploadFileMimeType, uploadFilePath)
VALUES (0, 'Placeholder: No File', 0, 'No File', '/var/www/none/');


CREATE TRIGGER beforeInsertUploadFile
BEFORE INSERT ON uploadFile
FOR EACH ROW
  SET new.uploadFileID = fnGetLUID('contentID');


DROP TABLE IF EXISTS rating;
CREATE TABLE rating
(
  raterID     BIGINT                              NOT NULL
  COMMENT 'Usually a UserID, but could be a uniquely identified algorithm, etc.',
  ratedID     BIGINT                              NOT NULL
  COMMENT 'The content, series, user, other rating, etc, that is being rated.',
  ratingStars TINYINT DEFAULT '0'                 NOT NULL
  COMMENT 'Currently picturing -2 through +2, with 0 meaning "3 stars"',
  ratingTitle VARCHAR(256)                        NULL,
  ratingText  TEXT                                NULL,
  createBy    BIGINT DEFAULT '0'                  NOT NULL,
  createTime  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy    BIGINT DEFAULT '0'                  NOT NULL,
  updateTime  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (raterID, ratedID)
);

DROP TABLE IF EXISTS session;
CREATE TABLE session
(
  sessionID    BIGINT DEFAULT '0'                  NOT NULL
    PRIMARY KEY,
  phpSessionID BINARY(43)                          NOT NULL,
  ipAddress    BINARY(45)                          NOT NULL
  COMMENT 'Storing outside of session data for convenience.',
  userID       BIGINT DEFAULT '0'                  NOT NULL,
  sessionData  TEXT                                NOT NULL,
  createBy     BIGINT DEFAULT '0'                  NOT NULL,
  createTime   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy     BIGINT DEFAULT '0'                  NOT NULL,
  updateTime   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT session_phpSessionID_uindex
  UNIQUE (phpSessionID)
);

INSERT INTO session (phpSessionID, ipAddress, sessionData)
VALUES ('0000000000000000000000000000000000000000000', '0000000000000000', '');

CREATE TRIGGER beforeInsertSessionLog
BEFORE INSERT ON session
FOR EACH ROW
  SET new.sessionID = fnGetLUID('sessionID');


DROP TABLE IF EXISTS tag;
CREATE TABLE tag
(
  tagID          BIGINT DEFAULT '0'                  NOT NULL
    PRIMARY KEY,
  tag            VARCHAR(128)                        NOT NULL,
  tagDescription TEXT                                NULL,
  createBy       BIGINT DEFAULT '0'                  NOT NULL,
  createTime     TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy       BIGINT DEFAULT '0'                  NOT NULL,
  updateTime     TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT tag_tag_uindex
  UNIQUE (tag)
);

INSERT INTO tag (tagID, tag, tagDescription)
VALUES (0, 'Non-tag', 'A placeholder tag with no meaning attached.');

CREATE TRIGGER beforeInsertTag
BEFORE INSERT ON tag
FOR EACH ROW
  SET new.tagID = fnGetLUID('tagID');

CREATE TRIGGER beforeUpdateTag
BEFORE UPDATE ON tag
FOR EACH ROW
  SET new.updateTime = CURRENT_TIMESTAMP;


DROP TABLE IF EXISTS thingTag;
CREATE TABLE thingTag
(
  thingTagID BIGINT DEFAULT '0'                  NOT NULL
    PRIMARY KEY,
  thingID    BIGINT                              NOT NULL
  COMMENT 'ID of the user, content item, or other thing having something bound to it.',
  tagID      BIGINT                              NOT NULL
  COMMENT 'ID of the Tag, Session, rating, or other thing beng attached.',
  createBy   BIGINT DEFAULT '0'                  NOT NULL,
  createTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy   BIGINT DEFAULT '0'                  NOT NULL,
  updateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT thingTag_thingID_tagID_pk
  UNIQUE (thingID, tagID)
)
  COMMENT 'TagUse can tag objects with Tags from the Tag table, or other LUIDs such as UserID to indicate ownership..';

CREATE TRIGGER beforeInsertThingTag
BEFORE INSERT ON thingTag
FOR EACH ROW
  SET new.thingTagID = fnGetLUID('thingTagID');

CREATE TRIGGER beforeUpdateThingTag
BEFORE UPDATE ON thingTag
FOR EACH ROW
  SET new.updateTime = CURRENT_TIMESTAMP;


DROP TABLE IF EXISTS user;
CREATE TABLE user
(
  userID     BIGINT DEFAULT '0'                  NOT NULL
    PRIMARY KEY,
  userEmail  VARCHAR(256)                        NOT NULL,
  userName   VARCHAR(256) DEFAULT 'Unknown'      NOT NULL,
  reputation SMALLINT(6) DEFAULT '10'            NOT NULL,
  sessionID  BIGINT DEFAULT '0'                  NOT NULL,
  saltHashID BIGINT                              NULL,
  createBy   BIGINT DEFAULT '0'                  NOT NULL,
  createTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  updateBy   BIGINT DEFAULT '0'                  NOT NULL,
  updateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT user_userEMail_uindex
  UNIQUE (userEmail),
  CONSTRAINT user_userName_uindex
  UNIQUE (userName)
)
  COMMENT 'Active status and LicenseAccepted are tags. LicenseAcceptDate is updateTime of TagUse for user / LicenseAccept';

INSERT INTO user (userEmail, userName, sessionID) VALUES ('nobody@nowhere.none', 'nobody', 0);

CREATE TRIGGER beforeInsertUser
BEFORE INSERT ON user
FOR EACH ROW
  BEGIN
    SET new.userID = fnGetLUID('userID');

    IF (IFNULL(new.userName, 'Unknown') = 'Unknown')
    THEN
      SET NEW.userName := NEW.userEmail;
    END IF;
  END;


DROP TABLE IF EXISTS saltHash;
CREATE TABLE saltHash
(
  saltHashID BIGINT DEFAULT '0'                                                                NOT NULL
    PRIMARY KEY,
  userID     BIGINT DEFAULT '0'                                                                NOT NULL,
  saltHash   BINARY(60) DEFAULT '000000000000000000000000000000000000000000000000000000000000' NOT NULL,
  accessTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP                                               NOT NULL
  COMMENT 'Timestamp of last access to this password. Accuracy questionable.',
  createBy   BIGINT DEFAULT '0'                                                                NOT NULL,
  createTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP                                               NOT NULL,
  updateBy   BIGINT DEFAULT '0'                                                                NOT NULL,
  updateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP                                               NOT NULL,
  CONSTRAINT saltHash_saltHash_uindex
  UNIQUE (saltHash)
);

INSERT INTO saltHash (saltHashID) VALUES (0);

CREATE TRIGGER beforeInsertPasswordHash
BEFORE INSERT ON saltHash
FOR EACH ROW
  SET new.saltHashID = fnGetLUID('saltHashID');


DROP FUNCTION IF EXISTS LogSession;
CREATE FUNCTION LogSession(theSessionID BINARY(43), theIPAddress VARCHAR(45), theUserID BIGINT)
  RETURNS BIGINT
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

    /*
    TO DO:
      - Refactor to storeIP only sessions as their own record.
      - If same session becomes known user session, log that.
         (Can relate where sessionID is same)
      - Also store session data in user table... Or refactor that too
         so we just pull it from the session log table
     */

    DECLARE result BIGINT;
    DECLARE existingSessionLogID BIGINT;
    DECLARE existingIPAdress BINARY(16);
    DECLARE existingUserID BIGINT;
    SET existingIPAdress = INET_ATON(theIPAddress);
    SELECT
      sessionID,
      userID
    INTO existingSessionLogID, existingUserID
    FROM session
    WHERE phpSessionID = theSessionID AND ipAddress = existingIPAdress
    LIMIT 1;

    IF (ISNULL(existingSessionLogID) || existingSessionLogID = 0)
    THEN
      IF (ISNULL(theUserID) || theUserID = 0)
      THEN
        INSERT INTO session (phpSessionID, ipAddress) VALUES (theSessionID, existingIPAdress);
      ELSE
        INSERT INTO session (phpSessionID, ipAddress, userID) VALUES (theSessionID, existingIPAdress, theUserID);
      END IF;
      SET result = LAST_INSERT_ID();
    ELSE
      IF ((!ISNULL(theUserID)) && (ISNULL(existingUserID)))
      THEN
        UPDATE session
        SET userID = theUserID
        WHERE sessionID = existingSessionLogID;
      END IF;
      SET result = existingSessionLogID;
    END IF;

    RETURN result;
  END;

DROP FUNCTION IF EXISTS tagCategoryIDFromTagID;
CREATE FUNCTION tagCategoryIDFromTagID(theTag BIGINT)
  RETURNS BIGINT
  BEGIN
    DECLARE Result BIGINT;
    DECLARE tagCategoryTagID BIGINT;

    SET tagCategoryTagID = (
      SELECT tagCategoryTagID());
    SET Result = (
      SELECT MyTagCategories.thingID
      FROM thingTag MyTagUse INNER JOIN thingTag MyTagCategories ON (
        MyTagUse.thingID = theTag AND
        MyTagCategories.thingID = MyTagUse.tagID AND
        MyTagCategories.tagID = tagCategoryTagID));

    RETURN Result;
  END;

DROP FUNCTION IF EXISTS tagCategoryIDFromTagText;
CREATE FUNCTION tagCategoryIDFromTagText(theTag VARCHAR(128))
  RETURNS BIGINT
  BEGIN
    RETURN tagCategoryIDFromTagID(tagIDFromText(theTag));
  END;

DROP FUNCTION IF EXISTS tagCategoryInsert;
CREATE FUNCTION tagCategoryInsert(theTag BIGINT, theCategory BIGINT, theUser BIGINT)
  RETURNS TINYINT
  BEGIN
    -- Test if category exists
    IF (tagIsTagCategory(theCategory) = 0)
    THEN RETURN -2;
    END IF;

    -- NOTE: This looks backwards, but what we are doing here is tagging
    --    a tag with a category. So, theTag is actually what is getting tagged,
    --    and theCategory is actually the tag.
    RETURN tagAttach(theTag, theCategory, theUser);
  END;


DROP FUNCTION IF EXISTS tagCategoryTagID;
CREATE FUNCTION tagCategoryTagID()
  RETURNS BIGINT
  RETURN IFNULL((
                  SELECT tagIDFromText('TagCategory')), 0);

DROP FUNCTION IF EXISTS tagCategoryTextFromTagID;
CREATE FUNCTION tagCategoryTextFromTagID(theTag BIGINT)
  RETURNS VARCHAR(128)
  BEGIN
    RETURN tagTextFromID(tagCategoryIDFromTagID(theTag));
  END;

DROP FUNCTION IF EXISTS tagCategoryTextFromTagText;
CREATE FUNCTION tagCategoryTextFromTagText(theTag VARCHAR(128))
  RETURNS VARCHAR(128)
  BEGIN
    RETURN tagCategoryTextFromTagID(tagIDFromText(theTag));
  END;

DROP FUNCTION IF EXISTS tagDelete;
CREATE FUNCTION tagDelete(theTagID BIGINT, theUser BIGINT)
  RETURNS INT
  BEGIN
    -- ------------------------------------------------------
    -- Deletes existing Tag.
    -- RETURNS:
    --   Success: The count of deleted TagUse rows... Hopefully 1.
    --   Failure:
    --     If user is not tagEditor: 0
    --     If tag is in use: 0 - Number of tag uses.
    --
    -- History:
    --  2017-07-14 J. Hawkins: Initial Version
    --  2017-10-30 J. Hawkins: Added tag protection check
    -- ------------------------------------------------------
    IF NOT (userIsTagEditor(theUser))
    THEN
      RETURN 0;
    END IF;

    -- Do not delete tags that are in (actual) use.
    DECLARE affectedRecordCount BIGINT;
    SET affectedRecordCount = (
      SELECT COUNT(*)
      FROM thingTag
      WHERE tagID = theTagID
    );

    -- NOTE: All tags have at least one use, being tagged with their category.
    IF (affectedRecordCount > 1)
    THEN
      RETURN 0 - affectedRecordCount;
    ELSE
      DELETE FROM tag
      WHERE tagID = theTagID;
      DELETE FROM thingTag
      WHERE (tagID = theTagID OR thingID = theTagID);
      RETURN ROW_COUNT();
    END IF;
  END;

DROP FUNCTION IF EXISTS tagIDFromText;
CREATE FUNCTION tagIDFromText(theTag VARCHAR(128))
  RETURNS BIGINT
  RETURN IFNULL((
                  SELECT tagID
                  FROM tag
                  WHERE tag = theTag), 0);

DROP FUNCTION IF EXISTS tagInsert;
CREATE FUNCTION tagInsert(newTag VARCHAR(128), newCategory BIGINT, newDescription TEXT, theUser BIGINT)
  RETURNS BIGINT
  BEGIN
    -- ------------------------------------------------------
    -- Creates a new tag and associates it's TagCategory.
    -- RETURNS:
    --   Success: New TagID
    --   Failure:
    --     User not permitted to edit tags: 0
    --     Unknown Error:           -1
    --     Category does not exist: -2
    --     Tag already exists:      -3
    --     Tag Insert failed:       -4
    --     TagUse Insert failed:    -5
    --
    -- REQUIRES:
    --   Category must already exist.
    --   Tag must not exist associated with the input
    --    category.
    --
    -- History:
    --  2017-07-12 J. Hawkins: Initial Version
    -- ------------------------------------------------------
    DECLARE Result BIGINT;

    -- Test if user is allowed to edit tags.
    IF NOT (userIsTagEditor(theUser))
    THEN
      RETURN 0;
    END IF;

    -- Test if category exists
    SET Result = tagIsTagCategory(newCategory);
    IF (Result = 0)
    THEN RETURN -2;
    END IF;

    -- Test if tag exists
    SET Result = tagIDFromText(newTag);
    IF (Result != 0)
    THEN RETURN -3;
    END IF;

    -- Insert the tag...
    INSERT INTO tag (tag, tagDescription, createBy, updateBy) VALUES (newTag, newDescription, theUser, theUser);
    SET Result = tagIDFromText(newTag);
    IF (Result < 1)
    THEN RETURN -4;
    END IF;

    -- Associate tag with category...
    IF (tagCategoryInsert(Result, newCategory, theUser) != 1)
    THEN RETURN -5;
    END IF;

    RETURN Result;
  END;


DROP FUNCTION IF EXISTS tagProtect;
CREATE FUNCTION tagProtect(theTag BIGINT, theUser BIGINT)
  RETURNS BIGINT
  BEGIN
    DECLARE result TINYINT;
    SET result = tagAttach(theTag, tagIDFromText('Protected'), theUser);

    IF (result > 0)
    THEN
      RETURN theTag;
    ELSE
      RETURN result;
    END IF;
  END;


DROP FUNCTION IF EXISTS tagIsTagCategory;
CREATE FUNCTION tagIsTagCategory(theTag BIGINT)
  RETURNS BIGINT
  BEGIN
    -- Returns Tag ID of TagCategory if it exists as a TagCategory. 0 If not.
    RETURN IFNULL((
                    SELECT thingID
                    FROM thingTag
                    WHERE thingID = theTag AND tagID = tagCategoryTagID()), 0);
  END;

DROP FUNCTION IF EXISTS tagTextFromID;
CREATE FUNCTION tagTextFromID(theTag BIGINT)
  RETURNS VARCHAR(128)
  RETURN IFNULL((
                  SELECT tag
                  FROM tag
                  WHERE tagID = theTag), '');

CREATE FUNCTION tagUpdate(theTagID BIGINT, newTag VARCHAR(128), newCategory BIGINT, newDescription TEXT, theUser BIGINT)
  RETURNS BIGINT
  BEGIN
    -- ------------------------------------------------------
    -- Updates existing Tag.
    -- RETURNS:
    --   Success: TagID
    --   Failure: 0
    --
    -- History:
    --  2017-07-12 J. Hawkins: Initial Version
    -- ------------------------------------------------------
    DECLARE oldCategory BIGINT;
    SET oldCategory = tagCategoryIDFromTagID(theTagID);

    -- Test if category exists
    IF (tagIsTagCategory(newCategory) = 0)
    THEN RETURN -1;
    END IF;

    -- Test if tag exists
    IF (tagTextFromID(theTagID) = '')
    THEN RETURN -2;
    END IF;

    -- Update the tag...
    UPDATE tag
    SET tag = newTag, tagDescription = newDescription, updateBy = theUser
    WHERE tagID = theTagID;
    -- TO DO: Research and address. Unsure if ROW_COUNT() will
    --   be > 0 if the update was category only... So, omitting
    --   this test for now.
    -- IF (ROW_COUNT() != 1) THEN RETURN -3;
    -- END IF;

    IF (oldCategory != newCategory)
    THEN
      IF (tagCategoryUpdate(newCategory, oldCategory, theTagID, theUser) < 0)
      THEN
        RETURN -4;
      END IF;
    END IF;
    RETURN theTagID;
  END;


DROP FUNCTION IF EXISTS userIDFromEmail;
CREATE FUNCTION userIDFromEmail(theEmail VARCHAR(128))
  RETURNS BIGINT
  RETURN IFNULL((
                  SELECT userID
                  FROM user
                  WHERE userEmail = theEmail), 0);


DROP FUNCTION IF EXISTS thingIsTagged;
CREATE FUNCTION thingIsTagged(theThing BIGINT, theTag BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF ((
          SELECT IFNULL(thingID, FALSE)
          FROM thingTag
          WHERE tagID = theTag AND thingID = theThing) > 0)
    THEN
      RETURN TRUE;
    ELSE
      RETURN FALSE;
    END IF;
  END;


DROP FUNCTION IF EXISTS userIsActive;
CREATE FUNCTION userIsActive(theUser BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF (thingIsTagged(theUser, tagIDFromText('Active')))
    THEN
      RETURN TRUE;
    END IF;

    RETURN FALSE;
  END;


DROP FUNCTION IF EXISTS userIsLicensed;
CREATE FUNCTION userIsLicensed(theUser BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF (thingIsTagged(theUser, tagIDFromText('LicenseAccepted')))
    THEN
      RETURN TRUE;
    END IF;

    RETURN FALSE;
  END;


DROP FUNCTION IF EXISTS userIsTagEditor;
CREATE FUNCTION userIsTagEditor(theUser BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF (thingIsTagged(theUser, tagIDFromText('TagEditor')))
    THEN
      RETURN TRUE;
    END IF;

    RETURN userIsSuperuser(theUser);
  END;


DROP FUNCTION IF EXISTS userIsContentEditor;
CREATE FUNCTION userIsContentEditor(theUser BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF (thingIsTagged(theUser, tagIDFromText('ContentEditor')))
    THEN
      RETURN TRUE;
    END IF;

    RETURN userIsSuperuser(theUser);
  END;

DROP FUNCTION IF EXISTS userIsSiteAdmin;
CREATE FUNCTION userIsSiteAdmin(theUser BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF (thingIsTagged(theUser, tagIDFromText('SiteAdmin')))
    THEN
      RETURN TRUE;
    END IF;

    RETURN userIsSuperuser(theUser);
  END;

DROP FUNCTION IF EXISTS userIsSuperuser;
CREATE FUNCTION userIsSuperuser(theUser BIGINT)
  RETURNS BOOLEAN
  BEGIN
    IF (thingIsTagged(theUser, tagIDFromText('Superuser')))
    THEN
      RETURN TRUE;
    END IF;

    RETURN FALSE;
  END;


DROP FUNCTION IF EXISTS addOrUpdateSession;
CREATE FUNCTION addOrUpdateSession(newSessionID BINARY(43), newSessionIPAddress BINARY(45), newSessionData TEXT)
  RETURNS BIGINT
  BEGIN
    -- ------------------------------------------------------
    -- Adds session record if not already present. Updates
    --  if existing record found.
    -- RETURNS:
    --   sessionID.
    -- ------------------------------------------------------
    IF (
      SELECT sessionID IS NOT NULL
      FROM session
      WHERE phpSessionID = newSessionID)
    THEN
      BEGIN
        -- Session should never change IP address... TO DO: Test for this.
        UPDATE session
        SET sessionData = newSessionData
        WHERE phpSessionID = newSessionID;
      END;
    ELSE
      BEGIN
        INSERT INTO session (phpSessionID, ipAddress, sessionData)
        VALUES (newSessionID, newSessionIPAddress, newSessionData);
      END;
    END IF;

    RETURN (
      SELECT sessionID
      FROM session
      WHERE phpSessionID = newSessionID);
  END;

DROP FUNCTION IF EXISTS addOrUpdateUser;
CREATE FUNCTION addOrUpdateUser(newUserEMail        VARCHAR(256), newSaltHash BINARY(60), newPHPSessionID BINARY(43),
                                newSessionIPAddress BINARY(43), newSessionData TEXT, theUpdateBy BIGINT)
  RETURNS BIGINT
  BEGIN
    -- ------------------------------------------------------
    -- Adds user if not already present. Updates
    --  if existing record found.
    -- RETURNS:
    --   0 if already present.
    --   New userID if added.
    --
    -- History:
    --  2017-07-07 J. Hawkins: Initial Version
    -- ------------------------------------------------------
    DECLARE newUserID BIGINT;
    DECLARE newSessionID BIGINT;
    DECLARE newSaltHashID BIGINT;
    DECLARE userExists BOOLEAN;

    -- TO DO: Check that updateBy is either the user being modified, or authorized to change other users.

    SET newSessionID = addOrUpdateSession(newPHPSessionID, newSessionIPAddress, newSessionData);
    SET newUserID = (
      SELECT userID
      FROM user
      WHERE userEmail = newUserEMail);
    SET userExists = (
      SELECT newUserID IS NOT NULL);

    IF (userExists)
    THEN
      BEGIN
        UPDATE user
        SET sessionID = newSessionID
        WHERE userID = newUserID;
      END;
    ELSE
      BEGIN
        -- Timestamps will all take care of themselves on initial insert.
        INSERT INTO user (userEmail, userName, sessionID) VALUES (newUserEMail, newUserEMail, newSessionID);
        SET newUserID = (
          SELECT userID
          FROM user
          WHERE userEmail = newUserEMail);
      END;
    END IF;

    -- Down here because this won't work until we know the user ID...
    SET newSaltHashID = addPasswordHash(newUserID, newSaltHash);

    SET theUpdateBy = IFNULL(theUpdateBy, newUserID); -- User assumed to update herself if not specified.

    IF (userExists)
    THEN
      UPDATE user
      SET saltHashID = newSaltHashID, updateBy = theUpdateBy
      WHERE userID = newUserID;
    ELSE
      UPDATE user
      SET saltHashID = newSaltHashID, updateBy = theUpdateBy, createBy = theUpdateBy
      WHERE userID = newUserID;
    END IF;

    RETURN newUserID;
  END;


DROP FUNCTION IF EXISTS addPasswordHash;
CREATE FUNCTION addPasswordHash(theUserID BIGINT, newSaltHash BINARY(60))
  RETURNS BIGINT
  BEGIN
    -- This will get called on some user updates... Ignore duplicates.
    INSERT IGNORE INTO saltHash (userID, saltHash) VALUES (theUserID, newSaltHash);
    RETURN (
      SELECT saltHashID
      FROM saltHash
      WHERE saltHash = newSaltHash);
  END;

DROP FUNCTION IF EXISTS permitUserRole;
CREATE FUNCTION permitUserRole(theUserID BIGINT, theUserRole VARCHAR(128))
  RETURNS BIGINT
  BEGIN
    RETURN (
      SELECT tagAttach(theUserID, tagIDFromText(theUserRole), theUserID)
    );
  END;

DROP FUNCTION IF EXISTS contentCanEdit;
CREATE FUNCTION contentCanEdit(theContentID BIGINT, theUserID BIGINT)
  RETURNS BOOLEAN
  BEGIN
    DECLARE canEdit BIGINT;
    SET canEdit = (
      SELECT createBy
      FROM content
      WHERE contentID = theContentID AND createBy = theUserID);
    IF (IFNULL(canEdit, 0) = 0)
    THEN
      SET canEdit = (
        SELECT thingID
        FROM thingTag
        WHERE thingID = theContentID AND tagID = theUserID);
    END IF;

    IF (IFNULL(canEdit, 0) = 0)
    THEN
      RETURN FALSE;
    ELSE
      RETURN TRUE;
    END IF;
  END;

DROP FUNCTION IF EXISTS uploadFileInsert;
CREATE FUNCTION uploadFileInsert(theUploadFileName    VARCHAR(256), theUploadFileSize INT,
                                 theploadFileMimeType VARCHAR(256), theUploadFilePath TEXT, theUser BIGINT)
  RETURNS BIGINT
  BEGIN
    INSERT INTO uploadFile (uploadFileID, uploadFileName, uploadFileSize, uploadFileMimeType, uploadFilePath, createBy, updateBy)
    VALUES (theUploadFileID, theUploadFileName, theUploadFileSize, theploadFileMimeType, theUploadFilePath, theUser,
            theUser);

    RETURN (SELECT MAX(uploadFileID) AS uploadFileID
            FROM uploadFile
            WHERE uploadFileName = theUploadFileName AND uploadFileSize = theUploadFileSize);
  END;



DROP FUNCTION IF EXISTS contentDelete;
CREATE FUNCTION contentDelete(theContentID BIGINT, theUserID BIGINT)
  RETURNS INT
  BEGIN
    IF (contentCanEdit(theContentID, theUserID))
    THEN
      DELETE FROM content
      WHERE contentID = theContentID;
      RETURN ROW_COUNT();
    ELSE
      RETURN -1;
    END IF;
  END;

DROP FUNCTION IF EXISTS contentInsert;
CREATE FUNCTION contentInsert(newTitle   VARCHAR(256), newDescription TEXT, newExcerpt TEXT,
                              newSummary TEXT, theUserID BIGINT)
  RETURNS BIGINT
  BEGIN
    DECLARE Result BIGINT;

    -- TO DO:
    -- Test if title exists
    -- Test if URL exists

    INSERT INTO content (contentTitle, contentDescription, contentExcerpt, contentSummary, createBy, updateBy)
    VALUES (newTitle, newDescription, newExcerpt, newSummary, theUserID, theUserID);

    -- TO DO: LAST_INSERT_ID() should be more efficient, but is not
    --  working here... Research if/when optimization is needed.
    --  Until then, just looking up the insert.

    -- TO DO: Work out issues around identical titles... Probably make titles unique.
    SET Result = (
      SELECT MAX(contentID) AS contentID
      FROM content
      WHERE contentTitle = newTitle);
    IF (Result < 1)
    THEN RETURN -3;
    END IF;

    RETURN Result;
  END;


DROP FUNCTION IF EXISTS contentUpdate;
CREATE FUNCTION contentUpdate(theContentID BIGINT, newTitle VARCHAR(256), newDescription TEXT, newExcerpt TEXT,
                              newSummary   TEXT, theUserID BIGINT)
  RETURNS BIGINT
  BEGIN
    -- Test if content record  exists
    IF ((
          SELECT contentID
          FROM content
          WHERE contentTitle = newTitle) < 1)
    THEN RETURN -2;
    END IF;

    IF NOT (contentCanEdit(theContentID, theUserID))
    THEN
      RETURN -1;
    END IF;

    UPDATE content
    SET
      contentTitle       = newTitle,
      contentDescription = newDescription,
      contentExcerpt     = newExcerpt,
      contentSummary     = newSummary,
      updateBy           = theUserID
    WHERE contentID = theContentID;
    -- TO DO: Research and address. Unsure if ROW_COUNT() will
    --   be > 0 if the update was category only... So, omitting
    --   this test for now.
    -- IF (ROW_COUNT() != 1) THEN RETURN -3;
    -- END IF;

    -- Replace tag category if different...
    -- TO DO: This should be it's own function.

    RETURN theContentID;
  END;


DROP PROCEDURE IF EXISTS procGetContentTags;
CREATE PROCEDURE procGetContentTags(theContentID BIGINT, theUserID BIGINT)
  BEGIN
    DECLARE canEdit BOOLEAN;
    IF (IFNULL(theUserID, 0) > 0)
    THEN
      SET canEdit = contentCanEdit(theContentID, theUserID);
    ELSE
      SET canEdit = FALSE;
    END IF;

    -- TO DO: Anticipate this needing optimization
    SELECT
      *,
      canEdit
    FROM (
           SELECT
             tagCategoryID,
             tagCategory,
             thingTag.tagID,
             tag
           FROM thingTag
             LEFT OUTER JOIN vTag ON thingTag.tagID = vTag.tagID
           WHERE thingTag.thingID = theContentID
           UNION
           SELECT
             0      AS tagCategoryID,
             'User' AS tagCategory,
             userID,
             userName
           FROM thingTag
             LEFT OUTER JOIN user ON thingTag.tagID = user.userID
           WHERE thingTag.thingID = theContentID
           UNION
           SELECT
             0      AS tagCategoryID,
             'User' AS tagCategory,
             content.createBy,
             userName
           FROM content
             LEFT OUTER JOIN user ON content.createBy = user.userID
           WHERE contentID = theContentID
         ) AS W1
    ORDER BY tagCategory, tag;
  END;


DROP PROCEDURE IF EXISTS procGetUserByEmail;
CREATE PROCEDURE procGetUserByEmail(IN theEmail VARCHAR(256))
  BEGIN
    -- Pass all similar requests to a single ID-based function
    CALL procGetUserByID((
                           SELECT userID
                           FROM user
                           WHERE userEmail = theEmail));
  END;

DROP PROCEDURE IF EXISTS procGetUserByID;
CREATE PROCEDURE procGetUserByID(theUserID BIGINT)
  BEGIN
    SELECT
      user.userID,
      userEmail,
      userName,
      saltHash,
      DATEDIFF(saltHash.updateTime, NOW()) AS saltHashAge,
      sessionData,
      reputation,
      userIsActive(theUserID)              AS isActive,
      userIsTagEditor(theUserID)           AS isTagEditor,
      userIsContentEditor(theUserID)       AS isContentEditor,
      userIsSiteAdmin(theUserID)           AS isSiteAdmin,
      userIsSuperuser(theUserID)           AS isSuperuser,
      userIsLicensed(theUserID)            AS isLicensed
    FROM user
      LEFT OUTER JOIN saltHash ON user.saltHashID = saltHash.saltHashID
      LEFT OUTER JOIN session ON session.sessionID = user.sessionID
    WHERE user.userID = theUserID;
  END;

DROP PROCEDURE IF EXISTS procGetUserByName;
CREATE PROCEDURE procGetUserByName(IN theName VARCHAR(256))
  BEGIN
    -- Pass all similar requests to a single ID-based function
    CALL procGetUserByID((
                           SELECT userID
                           FROM user
                           WHERE userName = theName));
  END;

DROP PROCEDURE IF EXISTS procGetUserForLogin;
CREATE PROCEDURE procGetUserForLogin(theEmail VARCHAR(256))
  BEGIN
    -- TO DO: Need more advanced login delay. procGetUserForLogin should
    --  return a result indicating delay is not complete if user attempts
    --  login before delay is complete. Ideally no delay up to 5 failures.
    --  Thereafter, initial delay of 5 seconds increases by 1 second for
    --  each further failed login attempt.
    --
    -- For now, just mitigate some brute force and DoS attacks by sleeping
    --  for 1 second with every attempt.
    -- DO SLEEP(1);
    CALL procGetUserByEmail(theEmail);
  END;


DROP PROCEDURE IF EXISTS procServerConfig;
CREATE PROCEDURE procServerConfig()
  BEGIN
    SELECT
      tagCategoryTagID()               AS tagCategoryTagID,
      tagIDFromText('Active')          AS tagActiveID,
      tagIDFromText('Inactive')        AS tagInactiveID,
      tagIDFromText('TagEditor')       AS tagEditorID,
      tagIDFromText('Superuser')       AS tagSuperuserID,
      tagIDFromText('LicenseAccepted') AS tagLicenseAccepted,
      CURRENT_TIMESTAMP()              AS sessionTimestamp;
  END;


DROP PROCEDURE IF EXISTS procViewContent;
CREATE PROCEDURE procViewContent(theContentID BIGINT, theUser BIGINT)
  BEGIN
    IF (IFNULL(theContentID, 0) != 0)
    THEN
      SELECT
        *,
        contentCanEdit(contentID, theUser) AS canEdit
      FROM vContent
      WHERE contentID = theContentID;
    ELSE
      SELECT
        *,
        contentCanEdit(contentID, theUser) AS canEdit
      FROM vContent
      WHERE contentID <> 0;
    END IF;
  END;


DROP PROCEDURE IF EXISTS procViewTags;
CREATE PROCEDURE procViewTags(theCategory VARCHAR(128))
  BEGIN
    IF (IFNULL(theCategory, '') != '')
    THEN
      SELECT *
      FROM vTag
      WHERE tagCategory = theCategory
      ORDER BY tagCategory, tag;
    ELSE
      SELECT *
      FROM vTag
      ORDER BY tagCategory, tag;
    END IF;
  END;

-- TO DO: Can't select from sproc in MySQL... Redesign.
/*
DROP FUNCTION IF EXISTS tagsToText;
CREATE FUNCTION tagsToText(theContentID BIGINT)
  RETURNS TEXT
  BEGIN
    SET group_concat_max_len = 4096;
    RETURN (SELECT GROUP_CONCAT(categorySet) AS tags
      FROM
        (SELECT CONCAT(tagCategory, ' (', GROUP_CONCAT(tag), ')') AS categorySet
          FROM (CALL procGetContentTags(theContentID)) W1
          GROUP BY tagCategory
          ORDER BY tagCategory
        ) W2
      GROUP BY categorySet);
  END;

SELECT tagsToText(100238);
*/


DROP FUNCTION IF EXISTS tagAttach;
CREATE FUNCTION tagAttach(theThing BIGINT, theTag BIGINT, theUser BIGINT)
  RETURNS BIGINT
  BEGIN
    DECLARE theNewThingTag BIGINT;

    -- Test if tag exists
    -- NOTE: All things can be tags, so check against LUID table instead of tag table.
    IF ((SELECT LUID
         FROM LUID
         WHERE LUID = theTag) > 0)
    THEN
      -- Apply theTag to theThing
      INSERT INTO thingTag (thingID, tagID, createBy, updateBy) VALUES (theThing, theTag, theUser, theUser);
      SET theNewThingTag = (
        SELECT MAX(thingTagID) AS theNewID
        FROM thingTag
        WHERE thingID = theThing
              AND tagID = theTag);

      IF (theNewThingTag > 0)
      THEN
        RETURN theNewThingTag;
      ELSE
        RETURN -2;
      END IF;
    ELSE
      RETURN -1;
    END IF;
  END;


DROP FUNCTION IF EXISTS tagCategoryUpdate;
CREATE FUNCTION tagCategoryUpdate(newCategory BIGINT, oldCategory BIGINT, theTagID BIGINT, theUser BIGINT)
  RETURNS BIGINT
  BEGIN
    UPDATE thingTag
    SET tagID = newCategory, updateBy = theUser
    WHERE tagID = oldCategory AND thingID = theTagID; -- thingID will be the ID of tag being updated.
    IF (ROW_COUNT() != 1)
    THEN
      RETURN -4;
    END IF;
    RETURN theTagID;
  END;


DROP FUNCTION IF EXISTS tagRemove;
CREATE FUNCTION tagRemove(theThing BIGINT, theTag BIGINT, theUser BIGINT)
  RETURNS TINYINT
  BEGIN
    DECLARE Result INT;
    SET Result = (
      SELECT COUNT(*)
      FROM thingTag
      WHERE tagID = theTag AND thingID = theThing);

    DELETE FROM thingTag
    WHERE tagID = theTag AND thingID = theThing;
    RETURN Result;
  END;


DROP VIEW IF EXISTS vContent;
CREATE VIEW vContent AS
  SELECT
    v4l.content.contentID          AS contentID,
    v4l.content.contentTitle       AS contentTitle,
    v4l.content.contentDescription AS contentDescription,
    v4l.content.contentExcerpt     AS contentExcerpt,
    v4l.content.contentSummary     AS contentSummary,
    v4l.content.createBy           AS createBy,
    v4l.content.createTime         AS createTime,
    v4l.content.updateBy           AS updateBy,
    v4l.content.updateTime         AS updateTime
  FROM v4l.content;


DROP VIEW IF EXISTS vTag;
CREATE VIEW vTag AS
  SELECT
    Category.tagID                AS tagCategoryID,
    Category.tag                  AS tagCategory,
    v4l.tag.tagID                 AS tagID,
    v4l.tag.tag                   AS tag,
    v4l.tag.tagDescription        AS tagDescription,
    v4l.tag.updateBy              AS updateBy,
    v4l.tag.updateTime            AS updateTime,
    (Protected.tagID IS NOT NULL) AS protected
  FROM ((((v4l.tag
    JOIN v4l.thingTag ON ((v4l.tag.tagID = v4l.thingTag.thingID))) JOIN v4l.thingTag CategoryUse
      ON (((v4l.thingTag.tagID = CategoryUse.thingID) AND
           (CategoryUse.tagID = tagCategoryTagID())))) JOIN v4l.tag Category
      ON ((CategoryUse.thingID = Category.tagID))) LEFT JOIN v4l.thingTag Protected
      ON (((v4l.tag.tagID = Protected.thingID) AND (Protected.tagID = tagIDFromText('Protected')))));


-- Insert required tags to support further creation of tags, etc.
INSERT INTO tag (tag, tagDescription)
VALUES ('TagCategory', 'Applied to another tag, indicates that tag is a tag category');
-- TagCategory is itself a tag category.
INSERT INTO thingTag (tagID, thingID) VALUES (tagCategoryTagID(), tagCategoryTagID());

-- Tags under the 'Status' tag category are used for system, row, content, and media status among other things.
DO tagInsert('Status', tagIDFromText('TagCategory'),
             'Indication of status including permission, such as Active or CanEdit.', 0);
DO tagInsert('Protected', tagIDFromText('Status'), 'Record is protected from edit or delete.', 0);
-- 'Protect' tag is in place, so can use tagProtect going forward.
DO tagProtect(tagIDFromText('TagCategory'), 0);
DO tagProtect(tagIDFromText('Status'), 0);
DO tagProtect(tagIDFromText('Protected'), 0);

-- Need status for graphics to indicate primary
DO tagProtect(
    tagInsert('ContentMainGraphic', tagIDFromText('Status'),
              'Tag to indicate main (logo) graphic for a content record.', 0),
    0
);



