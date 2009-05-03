/* 
Run this as the torrentflux user.

Example:
psql -d torrentflux tf_user -W -f pgsql_02_create_tables.sql
*/

CREATE SEQUENCE tf_links_sequence START 1;

CREATE TABLE tf_links (
  lid integer PRIMARY KEY DEFAULT nextval('tf_links_sequence'),
  url varchar(255) NOT NULL default '',
  sitename varchar(255) NOT NULL default 'Old Link',
  sort_order integer default '0'
);


/* data */

INSERT INTO tf_links VALUES (1, 'http://www.torrentflux.com', 'TorrentFlux.com', 0);


/* -------------------------------------------------------- */


/* Table structure for table `tf_log` */

CREATE SEQUENCE tf_log_sequence START 1;

CREATE TABLE tf_log (
  cid integer PRIMARY KEY DEFAULT nextval('tf_log_sequence'),
  user_id varchar(32) NOT NULL DEFAULT '',
  file varchar(200) NOT NULL DEFAULT '',
  action varchar(200) NOT NULL DEFAULT '',
  ip varchar(15) NOT NULL DEFAULT '',
  ip_resolved varchar(200) NOT NULL DEFAULT '',
  user_agent varchar(200) NOT NULL DEFAULT '',
  time varchar(14) NOT NULL DEFAULT '0'
);


/* Table structure for table `tf_messages` */

CREATE SEQUENCE tf_messages_sequence START 1;

-- column 'new' is now 'IsNew'
CREATE TABLE tf_messages (
  mid integer PRIMARY KEY default nextval('tf_messages_sequence'),
  to_user varchar(32) NOT NULL default '',
  from_user varchar(32) NOT NULL default '',
  message text,
  IsNew integer NOT NULL default '1',
  ip varchar(15) NOT NULL default '',
  time varchar(14) NOT NULL default '0',
  force_read smallint default '0'
);


/* Table structure for table `tf_rss` */

CREATE SEQUENCE tf_rss_sequence START 1;

CREATE TABLE tf_rss (
  rid integer PRIMARY KEY default nextval('tf_rss_sequence'),
  url varchar(255) NOT NULL default ''
);


/* Table structure for table `tf_settings` */

CREATE TABLE tf_settings (
  tf_key varchar(255) PRIMARY KEY NOT NULL default '',
  tf_value text NOT NULL
);

/* data */

INSERT INTO tf_settings VALUES ('path', '/usr/local/torrent/');
INSERT INTO tf_settings VALUES ('btphpbin', '/var/www/TF_BitTornado/btphptornado.py');
INSERT INTO tf_settings VALUES ('btshowmetainfo', '/var/www/TF_BitTornado/btshowmetainfo.py');
INSERT INTO tf_settings VALUES ('advanced_start', '1');
INSERT INTO tf_settings VALUES ('max_upload_rate', '10');
INSERT INTO tf_settings VALUES ('max_download_rate', '0');
INSERT INTO tf_settings VALUES ('max_uploads', '4');
INSERT INTO tf_settings VALUES ('minport', '49160');
INSERT INTO tf_settings VALUES ('maxport', '49300');
INSERT INTO tf_settings VALUES ('rerequest_interval', '1800');
INSERT INTO tf_settings VALUES ('cmd_options', '');
INSERT INTO tf_settings VALUES ('enable_search', '1');
INSERT INTO tf_settings VALUES ('enable_file_download', '1');
INSERT INTO tf_settings VALUES ('package_type', 'zip');
INSERT INTO tf_settings VALUES ('show_server_load', '1');
INSERT INTO tf_settings VALUES ('loadavg_path', '/proc/loadavg');
INSERT INTO tf_settings VALUES ('days_to_keep', '30');
INSERT INTO tf_settings VALUES ('minutes_to_keep', '3');
INSERT INTO tf_settings VALUES ('rss_cache_min', '20');
INSERT INTO tf_settings VALUES ('page_refresh', '60');
INSERT INTO tf_settings VALUES ('default_theme', 'matrix');
INSERT INTO tf_settings VALUES ('default_language', 'lang-english.php');
INSERT INTO tf_settings VALUES ('debug_sql', '1');
INSERT INTO tf_settings VALUES ('torrent_dies_when_done', 'False');
INSERT INTO tf_settings VALUES ('sharekill', '150');
INSERT INTO tf_settings VALUES ('tfQManager', '/var/www/TF_BitTornado/tfQManager.py');
INSERT INTO tf_settings VALUES ('AllowQueing', '0');
INSERT INTO tf_settings VALUES ('maxServerThreads', '5');
INSERT INTO tf_settings VALUES ('maxUserThreads', '2');
INSERT INTO tf_settings VALUES ('sleepInterval', '10');
INSERT INTO tf_settings VALUES ('debugTorrents', '0');
INSERT INTO tf_settings VALUES ('pythonCmd', '/usr/bin/python');
INSERT INTO tf_settings VALUES ('searchEngine', 'TorrentSpy');
INSERT INTO tf_settings VALUES ('TorrentSpyGenreFilter', 'a:3:{i:0;s:2:"11";i:1;s:1:"6";i:2;s:1:"7";}');
INSERT INTO tf_settings VALUES ('TorrentBoxGenreFilter', 'a:3:{i:0;s:1:"0";i:1;s:1:"9";i:2;s:2:"10";}');
INSERT INTO tf_settings VALUES ('TorrentPortalGenreFilter', 'a:3:{i:0;s:1:"0";i:1;s:1:"6";i:2;s:2:"10";}');
INSERT INTO tf_settings VALUES ('enable_maketorrent','0');
INSERT INTO tf_settings VALUES ('btmakemetafile','/var/www/TF_BitTornado/btmakemetafile.py');
INSERT INTO tf_settings VALUES ('enable_torrent_download','1');
INSERT INTO tf_settings VALUES ('enable_file_priority','1');
INSERT INTO tf_settings VALUES ('security_code','0');

/* -------------------------------------------------------- */


/* Table structure for table `tf_users` */

CREATE SEQUENCE tf_users_sequence START 1;

CREATE TABLE tf_users (
  uid integer PRIMARY KEY default nextval('tf_users_sequence'),
  user_id varchar(32) NOT NULL default '',
  password varchar(34) NOT NULL default '',
  hits integer NOT NULL default '0',
  last_visit varchar(14) NOT NULL default '0',
  time_created varchar(14) NOT NULL default '0',
  user_level smallint NOT NULL default '0',
  hide_offline smallint NOT NULL default '0',
  theme varchar(100) NOT NULL default 'mint',
  language_file varchar(60) default 'lang-english.php'
);


/* Table structure for table `tf_cookies` */

CREATE SEQUENCE tf_cookies_sequence START 1;

CREATE TABLE tf_cookies (
  cid integer PRIMARY KEY default nextval('tf_cookies_sequence'),
  uid integer NOT NULL,
  host varchar(255) default NULL,
  data varchar(255) default NULL
);
