
CREATE TABLE "menu" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "parent_id" integer NULL,
  "left" integer NOT NULL,
  "right" integer NOT NULL,
  "deep" integer NOT NULL DEFAULT 0,
  "title" text NOT NULL
);