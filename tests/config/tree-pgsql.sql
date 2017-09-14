CREATE TABLE menu (
  id serial NOT NULL,
  parent_id integer NULL,
  "left" integer NOT NULL,
  "right" integer NOT NULL,
  deep integer NOT NULL,
  title character varying(255) NOT NULL,
  CONSTRAINT menu_id PRIMARY KEY (id)
);