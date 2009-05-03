/* 
Run this script as the database superuser.
This should be postgres.

Run it thusly:
psql -d template1 -f pgsql_01_create_db_and_user.sql 
*/

/* create the database */
create database torrentflux;

/* This will prompt for a password */
create user tf_user password 'tfdefaultpassword';
