DROP SCHEMA IF EXISTS communityPress;

CREATE SCHEMA IF NOT EXISTS communityPress
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE communityPress;

-- Need root for this. Do at terminal.
-- SET GLOBAL log_bin_trust_function_creators = 1;

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
  updateTime         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  CONSTRAINT content_contentTitle_uindex
    UNIQUE (contentTitle)
);

INSERT INTO content (contentID, contentTitle, contentDescription, contentExcerpt, contentSummary)
VALUES (0, 'Placeholder: No Title', 'This is a placeholder record with no actual content attached.',
        'This is a placeholder record with no actual content attached.', 'CommunityPress.org');

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
  COMMENT 'Confirmed status and LicenseAccepted are tags. LicenseAcceptDate is updateTime of TagUse for user / LicenseAccept';

INSERT INTO user (userEmail, userName, sessionID) VALUES ('nobody@nowhere.none', 'Initial Setup', 0);

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
   */

  DECLARE result BIGINT;
  DECLARE existingSessionLogID BIGINT;
  DECLARE existingIPAdress BINARY(16);
  DECLARE existingUserID BIGINT;
  SET existingIPAdress = INET_ATON(theIPAddress);
  SELECT sessionID,
         userID
    INTO existingSessionLogID, existingUserID
  FROM session
  WHERE phpSessionID = theSessionID
    AND ipAddress = existingIPAdress
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
    FROM thingTag MyTagUse
           INNER JOIN thingTag MyTagCategories ON (
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
  RETURNS BIGINT
BEGIN
  -- Test if category exists
  IF (tagIsTagCategory(theCategory) = 0)
  THEN
    RETURN -2;
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
  DECLARE affectedRecordCount BIGINT;

  IF NOT (userIsTagEditor(theUser))
  THEN
    RETURN 0;
  END IF;

  -- Do not delete tags that are in (actual) use.
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
    DELETE
    FROM tag
    WHERE tagID = theTagID;
    DELETE
    FROM thingTag
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
  THEN
    RETURN -2;
  END IF;

  -- Test if tag exists
  SET Result = tagIDFromText(newTag);
  IF (Result != 0)
  THEN
    RETURN -3;
  END IF;

  -- Insert the tag...
  INSERT INTO tag (tag, tagDescription, createBy, updateBy) VALUES (newTag, newDescription, theUser, theUser);
  SET Result = tagIDFromText(newTag);
  IF (Result < 1)
  THEN
    RETURN -4;
  END IF;

  -- Associate tag with category...
  IF (tagCategoryInsert(Result, newCategory, theUser) < 1)
  THEN
    RETURN -5;
  END IF;

  RETURN Result;
END;


DROP FUNCTION IF EXISTS tagProtect;
CREATE FUNCTION tagProtect(theTag BIGINT, theUser BIGINT)
  RETURNS BIGINT
BEGIN
  DECLARE result BIGINT;
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
                  WHERE thingID = theTag
                    AND tagID = tagCategoryTagID()), 0);
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
  THEN
    RETURN -1;
  END IF;

  -- Test if tag exists
  IF (tagTextFromID(theTagID) = '')
  THEN
    RETURN -2;
  END IF;

  -- Update the tag...
  UPDATE tag
  SET tag            = newTag,
      tagDescription = newDescription,
      updateBy       = theUser
  WHERE tagID = theTagID;

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
        SELECT IFNULL(thingID, -1)
        FROM thingTag
        WHERE tagID = theTag
          AND thingID = theThing) > -1)
  THEN
    RETURN TRUE;
  ELSE
    RETURN FALSE;
  END IF;
END;


DROP FUNCTION IF EXISTS userIsConfirmed;
CREATE FUNCTION userIsConfirmed(theUser BIGINT)
  RETURNS BOOLEAN
BEGIN
  IF (thingIsTagged(theUser, tagIDFromText('Confirmed')))
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

  -- Check for password included in session. Fail if password included.
  IF (POSITION(";password|" IN newSessionData) > 0)
  THEN
    RETURN -1;
  END IF;

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
    SET saltHashID = newSaltHashID,
        updateBy   = theUpdateBy
    WHERE userID = newUserID;
  ELSE
    UPDATE user
    SET saltHashID = newSaltHashID,
        updateBy   = theUpdateBy,
        createBy   = theUpdateBy
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


DROP FUNCTION IF EXISTS contentCanEdit;
CREATE FUNCTION contentCanEdit(theContentID BIGINT, theUserID BIGINT)
  RETURNS BOOLEAN
BEGIN
  DECLARE canEdit BIGINT DEFAULT FALSE;
  DECLARE result BIGINT DEFAULT 0;

  -- If user is contentEditor, they may be permitted.
  IF (userIsContentEditor(theUserID))
  THEN
    -- If we are inserting a new item, permit contentEditor to act.
    IF (IFNULL(theContentID, 0) < 1)
    THEN
      SET canEdit = TRUE;
    ELSE
      -- If item was created by this user, permit contentEditor to act.
      SET result = (
        SELECT createBy
        FROM content
        WHERE contentID = theContentID
          AND createBy = theUserID);
      IF (IFNULL(result, 0) > 0)
      THEN
        SET canEdit = TRUE;
      ELSE
        -- It item is tagged with the user, permit contentEditor to act.
        SET result = (
          SELECT thingID
          FROM thingTag
          WHERE thingID = theContentID
            AND tagID = theUserID);
        IF (IFNULL(result, 0) > 0)
        THEN
          SET canEdit = TRUE;
        END IF;
      END IF;
    END IF;
  END IF;

  RETURN canEdit;
END;


DROP FUNCTION IF EXISTS uploadFileInsert;
CREATE FUNCTION uploadFileInsert(theUploadFileName    VARCHAR(256), theUploadFileSize INT,
                                 theploadFileMimeType VARCHAR(256), theUploadFilePath TEXT, theUser BIGINT)
  RETURNS BIGINT
BEGIN
  INSERT INTO uploadFile (uploadFileName, uploadFileSize, uploadFileMimeType, uploadFilePath, createBy, updateBy)
  VALUES (theUploadFileName, theUploadFileSize, theploadFileMimeType, theUploadFilePath, theUser,
          theUser);

  RETURN (
    SELECT MAX(uploadFileID) AS uploadFileID
    FROM uploadFile
    WHERE uploadFileName = theUploadFileName
      AND uploadFileSize = theUploadFileSize);
END;


DROP FUNCTION IF EXISTS uploadFileDelete;
CREATE FUNCTION uploadFileDelete(theUploadFileID BIGINT, theUserID BIGINT)
  RETURNS INT
BEGIN
  -- NOTE: Does not delete files.
  --       Delete files before deleting records with this function!!!
  DECLARE rowsAffected INT DEFAULT 0;
  DECLARE theContentRelationshipID BIGINT DEFAULT -1;
  DECLARE theContentID BIGINT DEFAULT -1;

  SELECT thingTag.thingTagID,
         content.contentID
    INTO theContentRelationshipID, theContentID
  FROM thingTag
         LEFT OUTER JOIN content ON thingTag.thingID = content.contentID
  WHERE thingTag.tagID = theUploadFileID;

  --    SET theContentID = (
  --      SELECT contentID
  --      FROM thingTag
  --        LEFT OUTER JOIN content ON thingTag.thingID = content.contentID
  --      WHERE thingTag.tagID = theUploadFileID);

  --    SET theContentRelationshipID = (
  --      SELECT thingTagID
  --      FROM thingTag
  --        WHERE thingID = theContentID AND tagID = theUploadFileID);

  IF (contentCanEdit(theContentID, theUserID))
  THEN
    -- Delete tags on the file or on relationship to this file.
    DELETE
    FROM thingTag
    WHERE
        thingTagID = theContentRelationshipID OR
        thingID = theContentRelationshipID OR
        tagID = theContentRelationshipID;
    SET rowsAffected = rowsAffected + ROW_COUNT();

    -- Delete the file record
    DELETE
    FROM uploadFile
    WHERE uploadFileID = theUploadFileID;
    SET rowsAffected = rowsAffected + ROW_COUNT();

    RETURN ROW_COUNT();
  ELSE
    RETURN 0;
  END IF;
END;


DROP FUNCTION IF EXISTS contentDelete;
CREATE FUNCTION contentDelete(theContentID BIGINT, theUserID BIGINT)
  RETURNS INT
BEGIN
  -- NOTE: Does not delete files.
  --       Delete files before deleting records with this function!!!
  DECLARE rowsAffected INT DEFAULT 0;

  IF (contentCanEdit(theContentID, theUserID))
  THEN
    -- Delete non-tag records first, since tag records are how we find them.
    -- Delete records for content and graphic files associated with this item.
    DELETE
    FROM uploadFile
    WHERE uploadFileID IN (
      SELECT *
      FROM (
             SELECT tagID
             FROM thingTag
             WHERE thingID = theContentID) AS toDelete);
    SET rowsAffected = rowsAffected + ROW_COUNT();

    -- Delete the content record
    DELETE
    FROM content
    WHERE contentID = theContentID;
    SET rowsAffected = rowsAffected + ROW_COUNT();

    -- Delete all tags indirectly related to this item... Tags on thingTag relationships.
    DELETE
    FROM thingTag
    WHERE thingID IN
          (
            SELECT *
            FROM (
                   SELECT thingTagID
                   FROM thingTag
                   WHERE thingID = theContentID
                      OR tagID = theContentID) AS toDelete);
    SET rowsAffected = rowsAffected + ROW_COUNT();

    -- Delete direct tags directly related to this item.
    DELETE
    FROM thingTag
    WHERE thingID = theContentID
       OR tagID = theContentID;
    SET rowsAffected = rowsAffected + ROW_COUNT();

    RETURN rowsAffected;
  ELSE
    RETURN 0;
  END IF;
END;


DROP FUNCTION IF EXISTS contentInsertUpdate;
CREATE FUNCTION contentInsertUpdate(theContentID BIGINT, theTitle VARCHAR(256), theDescription TEXT, theExcerpt TEXT,
                                    theSummary   TEXT, theUserID BIGINT)
  RETURNS BIGINT
  -- -----------------------------------------------------------------------------
  -- Author       Jeff Hawkins
  -- Created      2017/11/03
  -- Purpose      Update or insert content records, insuring permissions and
  --              content rules are followed.
  -- Copyright © 2017, Jeff Hawkins.
  --
  -- RETURN VALUES:
  --   SUCCESS: contentID of inserted or modified record.
  --   Failure:
  --     -1: Insert permission denied by site (not SQL) rules.
  --     -2: Update permission denied by site (not SQL) rules.
  --     -3: Title already exists. Must update instead of insert.
  --     -4: Record to be updated does not exist.
  --     Any other value < 1 (including null): Unknown Error
  --
  -- -----------------------------------------------------------------------------
  -- Modification History
  --
  -- 2017/11/03  Jeff Hawkins
  --      Initial version. Consolidates separate insert and update functions
  --      toward improved ability to maintain code.
  -- -----------------------------------------------------------------------------
BEGIN
  DECLARE bInsertMode BOOLEAN DEFAULT TRUE; -- TRUE for insert. FALSE for update.
  DECLARE bCanEdit BOOLEAN DEFAULT FALSE;

  IF (IFNULL(theContentID, 0) > 0)
  THEN
    SET bInsertMode = FALSE;
  END IF;

  SET bCanEdit = contentCanEdit(theContentID, theUserID);

  IF NOT (bCanEdit)
  THEN
    IF (bInsertMode)
    THEN
      RETURN -1; -- Insert permission denied by site (not SQL) rules.
    ELSE
      RETURN -2; -- Update permission denied by site (not SQL) rules.
    END IF;
  END IF;

  IF (bInsertMode)
  THEN
    IF (
      SELECT (IFNULL((
                       SELECT TRUE
                       FROM content
                       WHERE contentTitle = 'ThePig!'), FALSE)) AS bTitleExists)
    THEN
      RETURN -3; -- Title already exists. Must update instead of insert.
    END IF;

    INSERT INTO content (contentTitle, contentDescription, contentExcerpt, contentSummary, createBy, updateBy)
    VALUES (theTitle, theDescription, theExcerpt, theSummary, theUserID, theUserID);

    SET theContentID = (
      SELECT contentID
      FROM content
      WHERE contentTitle = theTitle);
  ELSE
    IF (
      SELECT (IFNULL((
                       SELECT FALSE
                       FROM content
                       WHERE contentID = theContentID), TRUE)) AS bRecordNotFound)
    THEN
      RETURN -4; -- Record to be updated does not exist.
    ELSE
      UPDATE content
      SET contentTitle       = theTitle,
          contentDescription = theDescription,
          contentExcerpt     = theExcerpt,
          contentSummary     = theSummary,
          updateBy           = theUserID
      WHERE contentID = theContentID;
    END IF;
  END IF;
  RETURN theContentID;
END;


DROP PROCEDURE IF EXISTS procGetContentFiles;
CREATE PROCEDURE procGetContentFiles(theContentID BIGINT, theUser BIGINT)
BEGIN
  IF (theContentID > 0)
  THEN
    SELECT uploadFileID,
           uploadFileName,
           uploadFileSize,
           uploadFileMimeType,
           uploadFilePath
    FROM uploadFile
           LEFT OUTER JOIN thingTag ON uploadFile.uploadFileID = thingTag.tagID
    WHERE thingTag.thingID = theContentID
    ORDER BY uploadFileName;
  END IF;
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
  SELECT *,
         canEdit
  FROM (
         SELECT tagCategoryID,
                tagCategory,
                thingTag.tagID,
                tag
         FROM thingTag
                LEFT OUTER JOIN vTag ON thingTag.tagID = vTag.tagID
         WHERE thingTag.thingID = theContentID
         UNION
         SELECT 0      AS tagCategoryID,
                'User' AS tagCategory,
                userID,
                userName
         FROM thingTag
                LEFT OUTER JOIN user ON thingTag.tagID = user.userID
         WHERE thingTag.thingID = theContentID
         UNION
         SELECT 0      AS tagCategoryID,
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
  SELECT user.userID,
         userEmail,
         userName,
         saltHash,
         DATEDIFF(saltHash.updateTime, NOW()) AS saltHashAge,
         sessionData,
         reputation,
         userIsConfirmed(theUserID)           AS isConfirmed,
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
  SELECT tagCategoryTagID()               AS tagCategoryTagID,
         tagIDFromText('Confirmed')       AS tagConfirmedID,
         tagIDFromText('TagEditor')       AS tagEditorID,
         tagIDFromText('Superuser')       AS tagSuperuserID,
         tagIDFromText('LicenseAccepted') AS tagLicenseAcceptedID,
         CURRENT_TIMESTAMP()              AS sessionTimestamp;
END;


DROP PROCEDURE IF EXISTS procViewAllContent;
CREATE PROCEDURE procViewAllContent(theUser BIGINT)
BEGIN
  SELECT *,
         contentAvatarID(contentID)         AS contentAvatarID,
         contentCanEdit(contentID, theUser) AS canEdit
  FROM vContent
  WHERE contentID > 0;
END;


DROP PROCEDURE IF EXISTS procViewContent;
CREATE PROCEDURE procViewContent(theContentID BIGINT, theUser BIGINT)
BEGIN
  IF (IFNULL(theContentID, -1) != -1)
  THEN
    SELECT contentID,
           contentTitle,
           contentDescription,
           contentExcerpt,
           contentSummary,
           createBy,
           createTime,
           updateBy,
           updateTime,
           contentAvatarID(theContentID)         AS contentAvatarID,
           contentCanEdit(theContentID, theUser) AS canEdit
    FROM vContent
    WHERE contentID = theContentID;
  ELSE -- If null, only canEdit has a value.
    SELECT NULL                                  AS contentID,
           NULL                                  AS contentTitle,
           NULL                                  AS contentDescription,
           NULL                                  AS contentExcerpt,
           NULL                                  AS contentSummary,
           NULL                                  AS createBy,
           NULL                                  AS createTime,
           NULL                                  AS updateBy,
           NULL                                  AS updateTime,
           NULL                                  AS contentAvatarID,
           contentCanEdit(theContentID, theUser) AS canEdit
    FROM vContent
    WHERE contentID = theContentID;
  END IF;
END;


DROP FUNCTION IF EXISTS contentAvatarID;
CREATE FUNCTION contentAvatarID(theContentID BIGINT)
  RETURNS BIGINT
BEGIN
  DECLARE contentAvatarFileUploadID BIGINT;
  DECLARE contentAvatarTagID BIGINT;

  SET contentAvatarTagID = (
    SELECT tagIDFromText('ContentAvatar'));
  SET contentAvatarFileUploadID = (
    SELECT contentThing.tagID
    FROM thingTag contentThing
           INNER JOIN thingTag avatarTag ON
      contentThing.thingTagID = avatarTag.thingID
    WHERE contentThing.thingID = theContentID
      AND avatarTag.tagID = contentAvatarTagID);
  RETURN contentAvatarFileUploadID;
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

  -- TO DO: Move permission check from permitUserRole (and elsewhere) to here. Check if tag category is of a type
  --   needing permission to use, then check permission before proceeding.
  IF ((
        SELECT LUID
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
  SET tagID    = newCategory,
      updateBy = theUser
  WHERE tagID = oldCategory
    AND thingID = theTagID; -- thingID will be the ID of tag being updated.
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
    WHERE tagID = theTag
      AND thingID = theThing);

  DELETE
  FROM thingTag
  WHERE tagID = theTag
    AND thingID = theThing;
  RETURN Result;
END;


DROP PROCEDURE IF EXISTS procTagCategories;
CREATE PROCEDURE procTagCategories()
BEGIN
  DECLARE tagCategoryTagID BIGINT;
  SET tagCategoryTagID = tagCategoryTagID();
  SELECT DISTINCT tag.tagID          AS tagCategoryID,
                  tag.tag            AS tagCategory,
                  tag.tagDescription AS tagCategoryDescription
  FROM tag
         JOIN thingTag ON
      tag.tagID = thingTag.thingID
      AND thingTag.tagID = tagCategoryTagID();
END;


DROP VIEW IF EXISTS vContent;
CREATE VIEW vContent AS
SELECT communityPress.content.contentID          AS contentID,
       communityPress.content.contentTitle       AS contentTitle,
       communityPress.content.contentDescription AS contentDescription,
       communityPress.content.contentExcerpt     AS contentExcerpt,
       communityPress.content.contentSummary     AS contentSummary,
       communityPress.content.createBy           AS createBy,
       communityPress.content.createTime         AS createTime,
       communityPress.content.updateBy           AS updateBy,
       communityPress.user.userName              AS updateByName,
       communityPress.content.updateTime         AS updateTime
FROM communityPress.content
       JOIN communityPress.user ON (content.updateBy = user.userID);


DROP VIEW IF EXISTS vTag;
CREATE VIEW vTag AS
SELECT DISTINCT Category.tagID                    AS tagCategoryID,
                Category.tag                      AS tagCategory,
                communityPress.tag.tagID          AS tagID,
                communityPress.tag.tag            AS tag,
                communityPress.tag.tagDescription AS tagDescription,
                communityPress.tag.updateBy       AS updateBy,
                user.userName                     AS updateByName,
                communityPress.tag.updateTime     AS updateTime,
                (Protected.tagID IS NOT NULL)     AS protected,
                (tagUsage.thingID IS NOT NULL)    AS inUse
FROM ((((((communityPress.tag
  JOIN communityPress.thingTag ON ((communityPress.tag.tagID = communityPress.thingTag.thingID)))
  -- Tags ate tagged with their category as indicated in ThingTag. So CategoryUse is an intermediary table used to
  -- get back to the actual category name.
  JOIN communityPress.thingTag CategoryUse
  ON (((communityPress.thingTag.tagID = CategoryUse.thingID) AND (CategoryUse.tagID = tagCategoryTagID()))))
  JOIN communityPress.tag Category ON ((CategoryUse.thingID = Category.tagID)))
  JOIN communityPress.user ON ((tag.updateBy = user.userID)))
  LEFT JOIN communityPress.thingTag Protected
  ON (((communityPress.tag.tagID = Protected.thingID) AND (Protected.tagID = tagIDFromText('Protected'))))
  LEFT JOIN thingTag tagUsage ON (tag.tagID = tagUsage.tagID))
       );

-- Insert required tags to support further creation of tags, etc.
INSERT INTO tag (tag, tagDescription)
VALUES ('TagCategory', 'Applied to another tag, indicates that tag is a tag category.'),
       ('Status', 'Indication of status including permission, such as Confirmed or CanEdit.'),
       ('Superuser', 'Superuser has unrestricted ability to modify site data and content, and must be very careful.');

-- TagCategory is itself a tag category. Set category for initial tags.
INSERT INTO thingTag (thingID, tagID) VALUES (tagCategoryTagID(), tagCategoryTagID()),
                                             (tagIDFromText('Status'), tagCategoryTagID()),
                                             (tagIDFromText('Superuser'), tagIDFromText('Status')),
                                             -- Temporarily make user zero a superuser to enable tagInsert permissions for the rest of initial setup.
                                             (0, tagIDFromText('Superuser'));

-- Tags under the 'Status' tag category are used for system, row, content, and media status among other things.
DO tagInsert('Protected', tagIDFromText('Status'), 'Record is protected from edit or delete.', 0);
DO tagInsert('Confirmed', tagIDFromText('Status'), 'User ID (typically email) is confirmed.', 0);
DO tagInsert('LicenseAccepted', tagIDFromText('Status'),
             'User has accepted the license. Date accepted is tag creation date.', 0);
DO tagInsert('SiteAdmin', tagIDFromText('Status'),
             'Site admin or developer. Can edit most data, including user info and status. ', 0);
DO tagInsert('TagEditor', tagIDFromText('Status'), 'Has permission to create and edit Tags.', 0);
DO tagInsert('ContentEditor', tagIDFromText('Status'), 'Has permission to create and edit content.', 0);

-- 'Protect' tag is in place, so can use tagProtect going forward.
DO tagProtect(tagIDFromText('TagCategory'), 0);
DO tagProtect(tagIDFromText('Status'), 0);
DO tagProtect(tagIDFromText('Protected'), 0);
DO tagProtect(tagIDFromText('Confirmed'), 0);
DO tagProtect(tagIDFromText('LicenseAccepted'), 0);
DO tagProtect(tagIDFromText('Superuser'), 0);
DO tagProtect(tagIDFromText('SiteAdmin'), 0);
DO tagProtect(tagIDFromText('TagEditor'), 0);
DO tagProtect(tagIDFromText('ContentEditor'), 0);

-- Need status for graphics to indicate content avatar
DO tagProtect(
    tagInsert('ContentAvatar', tagIDFromText('Status'),
              'Tag to indicate main (logo) graphic for a content record.', 0),
    0
  );


DROP FUNCTION IF EXISTS permitUserRole;
CREATE FUNCTION permitUserRole(grantTo BIGINT, theUserRole VARCHAR(128), grantBy BIGINT)
  RETURNS BIGINT
BEGIN
  IF (userIsSuperuser(grantBy)) -- If grantBy has permission to do this.
  THEN
    RETURN (
      SELECT tagAttach(grantTo, tagIDFromText(theUserRole), grantBy));
  ELSE
    RETURN -1;
  END IF;
END;


DROP FUNCTION IF EXISTS userGrantSuperuser;
CREATE FUNCTION userGrantSuperuser(grantTo BIGINT, grantBy BIGINT)
  RETURNS INT
BEGIN
  RETURN permitUserRole(grantTo, 'Superuser', grantBy);
END;

DROP FUNCTION IF EXISTS userGrantContentEditor;
CREATE FUNCTION userGrantContentEditor(grantTo BIGINT, grantBy BIGINT)
  RETURNS INT
BEGIN
  RETURN permitUserRole(grantTo, 'ContentEditor', grantBy);
END;


DROP FUNCTION IF EXISTS revokeUserRole;
CREATE FUNCTION revokeUserRole(revokeFrom BIGINT, userRoleID BIGINT, revokeBy BIGINT)
  RETURNS BIGINT
BEGIN
  DECLARE permissionTagID BIGINT;

  IF (userIsSuperuser(revokeBy))
      THEN
    SET permissionTagID = (
      SELECT thingTagID
      FROM thingTag
      WHERE thingID = revokeFrom AND tagID = userRoleID);
    IF (permissionTagID > 0)
    THEN
      DELETE FROM thingTag
      WHERE thingID = revokeFrom AND tagID = userRoleID;
      RETURN permissionTagID;
    ELSE
      RETURN -1;
    END IF;
  ELSE
    RETURN 0;
  END IF;
END;


DROP FUNCTION IF EXISTS userRevokeSuperuser;
CREATE FUNCTION userRevokeSuperuser(revokeFrom BIGINT, revokeBy BIGINT)
  RETURNS BIGINT
BEGIN
  RETURN revokeUserRole(revokeFrom, tagIDFromText('Superuser'), revokeBy);
END;


DROP FUNCTION IF EXISTS userRevokeContentEditor;
CREATE FUNCTION userRevokeContentEditor(revokeFrom BIGINT, revokeBy BIGINT)
  RETURNS BIGINT
BEGIN
  RETURN revokeUserRole(revokeFrom, tagIDFromText('ContentEditor'), revokeBy);
END;

/* -- ---------------------------------------------------------------------------------------------------------------
-- AT THIS POINT: Administrator should self-register an account via the web site. then return here to continue data
--                setup under their own account.
--
-- IMPORTANT:     Superuser privileges must be removed from user zero.
--
SELECT userGrantSuperuser(userIDFromEmail('codehawkins.webmaster@gmail.com'), 0);
SELECT userRevokeSuperuser(0, 0);

-- Test:
SELECT userIsSuperuser(userIDFromEmail('codehawkins.webmaster@gmail.com'));
SELECT userIsSuperuser(0);

-- Further setup should be under the authorized account, such as:
SELECT userGrantContentEditor(userIDFromEmail('jhawkins128@gmail.com'), userIDFromEmail('codehawkins.webmaster@gmail.com'));
SELECT permitUserRole(userIDFromEmail('jhawkins128@gmail.com'), 'TagEditor', userIDFromEmail('codehawkins.webmaster@gmail.com'));
--
*/ -- ---------------------------------------------------------------------------------------------------------------
