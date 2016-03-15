CREATE OR REPLACE FUNCTION check_purchased(our_show_id IN VARCHAR, our_avatar_uuid IN VARCHAR) RETURNS BOOLEAN AS $$
DECLARE
purchase_result VARCHAR;
BEGIN
SELECT INTO purchase_result show_id FROM purchase_table WHERE show_id = our_show_id AND avatar_uuid = our_avatar_uuid;
if purchase_result IS DISTINCT FROM our_show_id THEN
RETURN FALSE;
END IF;
RETURN TRUE;
END $$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_purchasers(our_show_id IN VARCHAR) RETURNS VARCHAR[] AS $$
DECLARE
purchasers VARCHAR[];
BEGIN
purchasers := array(SELECT purchase_table.avatar_uuid FROM show_table JOIN purchase_table ON
show_table.show_id = purchase_table.show_id WHERE purchase_table.show_id = our_show_id);
RETURN purchasers;
END $$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_filename(our_show_id IN VARCHAR) returns VARCHAR as $$
DECLARE
	our_filename VARCHAR;
BEGIN
	SELECT INTO our_filename file_name FROM show_table WHERE show_id = our_show_id;
	IF NOT FOUND THEN
		RETURN FALSE;
	END IF;
	RETURN our_filename;
END $$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_filename(our_show_id IN VARCHAR, our_new_name IN VARCHAR)
	RETURNS VARCHAR AS $$
DECLARE
	our_filename VARCHAR;
	fn_extension VARCHAR;
BEGIN
	SELECT INTO fn_extension our_extension FROM curr_extension;
	our_filename := replace(our_new_name, ' ', '_');
	our_filename := our_filename || '_';
	our_filename := our_filename || to_char(now(), 'MM');
	our_filename := our_filename || to_char(now(), 'DD');
	our_filename := our_filename || to_char(now(), 'YYYY');
	our_filename :=	our_filename || fn_extension;
	UPDATE show_table SET file_name = our_filename WHERE show_id = our_show_id;
	IF FOUND THEN
		RETURN our_filename;
	END IF;
END $$
LANGUAGE plpgsql;
	
CREATE OR REPLACE FUNCTION init_download(our_link_id IN VARCHAR, our_ip_address in INET) 
	RETURNS VARCHAR AS $$
DECLARE
	our_filename VARCHAR;
BEGIN
	SELECT INTO our_filename show_table.file_name FROM show_table JOIN purchase_table ON
	purchase_table.show_id = show_table.show_id WHERE purchase_table.link_id = our_link_id;
IF NOT FOUND THEN
	RETURN FALSE;
END IF;
	UPDATE purchase_table SET download_count = download_count + 1 WHERE link_id = our_link_id;
	INSERT INTO download_log_table (link_id, ip_address) VALUES (our_link_id, our_ip_address); 
	RETURN our_filename;
END $$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION datecode_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
        our_datecode VARCHAR;
BEGIN
        our_datecode := to_char(now(), 'MM');
        our_datecode := our_datecode || to_char(now(), 'DD');
        our_datecode := our_datecode || to_char(now(), 'YYYY');
		new.date_code = our_datecode;
RETURN new;
END;
$$;

CREATE OR REPLACE FUNCTION linkid_trigger() RETURNS trigger
	LANGUAGE plpgsql
	AS $$
DECLARE
	our_linkid VARCHAR;
BEGIN
	our_linkid := left(encode(digest(new.avatar_uuid::text, 'sha1'), 'hex'), 8);
	our_linkid := our_linkid || left(encode(digest(new.show_id::text, 'sha1'), 'hex'), 8);
	new.link_id = our_linkid;
RETURN new;
END;
$$;
	
CREATE OR REPLACE FUNCTION filename_trigger() RETURNS trigger
	LANGUAGE plpgsql
	AS $$
DECLARE
	our_filename VARCHAR;
	proc_region_name VARCHAR;
	fn_extension VARCHAR;
BEGIN
	SELECT INTO fn_extension our_extension FROM curr_extension;
	proc_region_name := replace(new.region_name, ' ', '_');
	our_filename := proc_region_name || '_';
	our_filename := our_filename || to_char(now(), 'MM');
	our_filename := our_filename || to_char(now(), 'DD');
	our_filename := our_filename || to_char(now(), 'YYYY');
	our_filename := our_filename || '_' || substr(new.show_id, 7, 2) || fn_extension;
	new.file_name = our_filename;
RETURN new;
END;
$$;

CREATE OR REPLACE FUNCTION reset_filename(our_show_id VARCHAR) RETURNS VARCHAR AS $$
DECLARE
	new_filename VARCHAR;
	our_region_name VARCHAR;
	fn_extension VARCHAR;
BEGIN
	SELECT INTO our_region_name region_name FROM show_table WHERE show_id = our_show_id;
	SELECT INTO fn_extension our_extension FROM curr_extension;
	new_filename := our_region_name || '_';
	new_filename := new_filename || to_char(now(), 'MM');
	new_filename := new_filename || to_char(now(), 'DD');
	new_filename := new_filename || to_char(now(), 'YYYY');
	new_filename := new_filename || '_' || substr(our_show_id, 7, 2) || fn_extension;
	UPDATE show_table SET file_name = new_filename WHERE show_id = our_show_id;
	IF FOUND THEN
		RETURN new_filename;
	END IF;
END $$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION showid_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
        our_random VARCHAR;
BEGIN
        our_random := array_to_string(ARRAY(SELECT chr((48 + round(random() * 9)) :: integer) FROM generate_series(1,8)), '');
        new.show_id = our_random;
RETURN new;
END;
$$;

CREATE OR REPLACE FUNCTION log_showid_linkid_trigger() RETURNS trigger
	LANGUAGE plpgsql
	AS $$
DECLARE
	our_show_id VARCHAR;
BEGIN
	SELECT INTO our_show_id show_id FROM purchase_table WHERE link_id = new.link_id;
	IF NOT FOUND THEN
		our_show_id := '(none)';
	END IF;
	new.show_id = our_show_id;
RETURN new;
END;
$$;
