grant execute on newsfeed.* to newsfeed@'%'

delimiter $$
create or replace procedure set_queue(
    in _id integer
)
begin
    insert into source_queue (s_id)
    select s_id from sources 
    where 
        s_active=1 and (case when _id<>0 then s_id=_id else true end)
    order by s_id asc;
end$$
delimiter ;

delimiter $$
create or replace procedure new_link(
    in _title varchar(255),
    in _hash varchar(255),
    in _description varchar(255),
    in _image_url varchar(255),
    in _image_local varchar(255),
    in _date datetime,
    in _base_url varchar(255),
    in _origin_url varchar(255),
    in _content text
)
begin
    insert into dashboard_sites (
        ds_title, 
        ds_hash, 
        ds_description, 
        ds_image_url, 
        ds_image_local, 
        ds_date, 
        ds_base_url, 
        ds_origin_url,
        ds_content
    ) value (
        _title, 
        _hash, 
        _description, 
        _image_url, 
        _image_local, 
        _date, 
        _base_url, 
        _origin_url,
        _content
    );
end$$
delimiter ;

delimiter $$
create or replace procedure update_link(
    in _id integer,
    in _title varchar(255),
    in _hash varchar(255),
    in _description varchar(255),
    in _image_url varchar(255),
    in _image_local varchar(255),
    in _date datetime,
    in _base_url varchar(255),
    in _origin_url varchar(255),
    in _content text
)
begin

    declare _ds_id integer default null;

    declare _ds_title varchar(255) default null;
    declare _ds_description varchar(255) default null;
    declare _ds_image_url varchar(255) default null;
    declare _ds_image_local varchar(255) default null;
    declare _ds_date datetime default null;
    declare _ds_content text default null;

    select ds_id into _ds_id from dashboard_sites where ds_hash like _hash;

    case when _title is null then 
            select ds_title into _ds_title from dashboard_sites where ds_id = _ds_id;
        else 
            set _ds_title = _title;
    end case;

    case when _description is null then 
            select ds_description into _ds_description from dashboard_sites where ds_id = _ds_id;
        else 
            set _ds_description = _description;
    end case;

    case when _image_url is null then 
            select ds_image_url into _ds_image_url from dashboard_sites where ds_id = _ds_id;
        else 
            set _ds_image_url = _image_url;
    end case;

    case when _image_local is null then 
            select ds_image_local into _ds_image_local from dashboard_sites where ds_id = _ds_id;
        else 
            set _ds_image_local = _image_local;
    end case;

    case when _date is null then 
            select ds_date into _ds_date from dashboard_sites where ds_id = _ds_id;
        else 
            set _ds_date = _date;
    end case;

    case when _content is null then 
            select ds_content into _ds_content from dashboard_sites where ds_id = _ds_id;
        else 
            set _ds_content = _content;
    end case;

    update dashboard_sites set 
        ds_description = _ds_description, 
        ds_image_url = _ds_image_url, 
        ds_image_local = _ds_image_local, 
        ds_date = _ds_date, 
        ds_content = _ds_content
    where 
        ds_id = _ds_id;
end$$
delimiter ;

call update_link(null,null,'86042136c4515eb4',null,null,null,null,null,null,null);