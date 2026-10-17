/*
 Navicat Premium Dump SQL

 Source Server         : localhost_5432
 Source Server Type    : PostgreSQL
 Source Server Version : 180006 (180006)
 Source Host           : localhost:5432
 Source Catalog        : ffff
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 180006 (180006)
 File Encoding         : 65001

 Date: 17/10/2026 10:23:59
*/


-- ----------------------------
-- Sequence structure for admin_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."admin_id_seq";
CREATE SEQUENCE "public"."admin_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for discord_webhook_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."discord_webhook_id_seq";
CREATE SEQUENCE "public"."discord_webhook_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for kandidat_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."kandidat_id_seq";
CREATE SEQUENCE "public"."kandidat_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for suara_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."suara_id_seq";
CREATE SEQUENCE "public"."suara_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS "public"."admin";
CREATE TABLE "public"."admin" (
  "id" int4 NOT NULL DEFAULT nextval('admin_id_seq'::regclass),
  "username" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "password_hash" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "nama" varchar(100) COLLATE "pg_catalog"."default",
  "created_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for discord_webhook
-- ----------------------------
DROP TABLE IF EXISTS "public"."discord_webhook";
CREATE TABLE "public"."discord_webhook" (
  "id" int4 NOT NULL DEFAULT nextval('discord_webhook_id_seq'::regclass),
  "nama" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "deskripsi" varchar(255) COLLATE "pg_catalog"."default",
  "url" text COLLATE "pg_catalog"."default" NOT NULL,
  "aktif" bool DEFAULT true,
  "created_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for kandidat
-- ----------------------------
DROP TABLE IF EXISTS "public"."kandidat";
CREATE TABLE "public"."kandidat" (
  "id" int4 NOT NULL DEFAULT nextval('kandidat_id_seq'::regclass),
  "nama" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "kelas" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "visi" varchar(255) COLLATE "pg_catalog"."default",
  "foto" varchar(255) COLLATE "pg_catalog"."default",
  "misi" text COLLATE "pg_catalog"."default",
  "program" text COLLATE "pg_catalog"."default",
  "quote" varchar(255) COLLATE "pg_catalog"."default",
  "ttl" varchar(100) COLLATE "pg_catalog"."default",
  "motivasi" text COLLATE "pg_catalog"."default",
  "created_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for pengaturan
-- ----------------------------
DROP TABLE IF EXISTS "public"."pengaturan";
CREATE TABLE "public"."pengaturan" (
  "key" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "value" text COLLATE "pg_catalog"."default",
  "updated_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for siswa
-- ----------------------------
DROP TABLE IF EXISTS "public"."siswa";
CREATE TABLE "public"."siswa" (
  "nis" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "nama" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "kelas" varchar(50) COLLATE "pg_catalog"."default",
  "sudah_memilih" bool DEFAULT false
)
;

-- ----------------------------
-- Table structure for suara
-- ----------------------------
DROP TABLE IF EXISTS "public"."suara";
CREATE TABLE "public"."suara" (
  "id" int4 NOT NULL DEFAULT nextval('suara_id_seq'::regclass),
  "nis" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "kandidat_id" int4 NOT NULL,
  "waktu" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "ip_address" varchar(45) COLLATE "pg_catalog"."default",
  "user_agent" varchar(255) COLLATE "pg_catalog"."default",
  "device_id" varchar(64) COLLATE "pg_catalog"."default",
  "screen_res" varchar(20) COLLATE "pg_catalog"."default",
  "timezone" varchar(50) COLLATE "pg_catalog"."default",
  "language" varchar(20) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."admin_id_seq"
OWNED BY "public"."admin"."id";
SELECT setval('"public"."admin_id_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."discord_webhook_id_seq"
OWNED BY "public"."discord_webhook"."id";
SELECT setval('"public"."discord_webhook_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."kandidat_id_seq"
OWNED BY "public"."kandidat"."id";
SELECT setval('"public"."kandidat_id_seq"', 3, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."suara_id_seq"
OWNED BY "public"."suara"."id";
SELECT setval('"public"."suara_id_seq"', 1, false);

-- ----------------------------
-- Uniques structure for table admin
-- ----------------------------
ALTER TABLE "public"."admin" ADD CONSTRAINT "admin_username_key" UNIQUE ("username");

-- ----------------------------
-- Primary Key structure for table admin
-- ----------------------------
ALTER TABLE "public"."admin" ADD CONSTRAINT "admin_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Uniques structure for table discord_webhook
-- ----------------------------
ALTER TABLE "public"."discord_webhook" ADD CONSTRAINT "discord_webhook_nama_key" UNIQUE ("nama");

-- ----------------------------
-- Primary Key structure for table discord_webhook
-- ----------------------------
ALTER TABLE "public"."discord_webhook" ADD CONSTRAINT "discord_webhook_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table kandidat
-- ----------------------------
ALTER TABLE "public"."kandidat" ADD CONSTRAINT "kandidat_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table pengaturan
-- ----------------------------
ALTER TABLE "public"."pengaturan" ADD CONSTRAINT "pengaturan_pkey" PRIMARY KEY ("key");

-- ----------------------------
-- Primary Key structure for table siswa
-- ----------------------------
ALTER TABLE "public"."siswa" ADD CONSTRAINT "siswa_pkey" PRIMARY KEY ("nis");

-- ----------------------------
-- Indexes structure for table suara
-- ----------------------------
CREATE INDEX "idx_suara_device" ON "public"."suara" USING btree (
  "device_id" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "idx_suara_kandidat" ON "public"."suara" USING btree (
  "kandidat_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "idx_suara_nis" ON "public"."suara" USING btree (
  "nis" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table suara
-- ----------------------------
ALTER TABLE "public"."suara" ADD CONSTRAINT "suara_nis_key" UNIQUE ("nis");

-- ----------------------------
-- Primary Key structure for table suara
-- ----------------------------
ALTER TABLE "public"."suara" ADD CONSTRAINT "suara_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Foreign Keys structure for table suara
-- ----------------------------
ALTER TABLE "public"."suara" ADD CONSTRAINT "fk_kandidat" FOREIGN KEY ("kandidat_id") REFERENCES "public"."kandidat" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
