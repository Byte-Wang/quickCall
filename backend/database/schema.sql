-- =============================================================
-- 快拨通讯录 · MySQL 建表脚本
-- 表结构在首次运行后端时会自动执行（CREATE TABLE IF NOT EXISTS）；
-- 数据库需提前创建，可先创建数据库后执行本文件：
--   CREATE DATABASE IF NOT EXISTS quickdial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE quickdial;
--   SOURCE /path/to/schema.sql;
-- =============================================================

-- 用户表
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  phone VARCHAR(20) NOT NULL COMMENT '手机号（登录账号）',
  password_hash VARCHAR(255) NOT NULL COMMENT '密码哈希',
  register_ip VARCHAR(45) NOT NULL DEFAULT '' COMMENT '注册IP',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_phone (phone),
  KEY idx_users_register_ip_created (register_ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户';

-- 登录令牌表
CREATE TABLE IF NOT EXISTS auth_tokens (
  token VARCHAR(64) NOT NULL COMMENT 'Bearer Token',
  user_id INT UNSIGNED NOT NULL COMMENT '所属用户',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (token),
  KEY idx_auth_tokens_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='登录令牌';

-- 拨号页表
CREATE TABLE IF NOT EXISTS dial_pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  user_id INT UNSIGNED NOT NULL COMMENT '所属用户',
  name VARCHAR(100) NOT NULL COMMENT '拨号页名称',
  slug VARCHAR(32) NOT NULL COMMENT '唯一分享标识',
  bg_type VARCHAR(16) NOT NULL DEFAULT 'color' COMMENT '背景类型：color/image',
  bg_color VARCHAR(16) NOT NULL DEFAULT '#0f172a' COMMENT '背景色',
  bg_image VARCHAR(255) NOT NULL DEFAULT '' COMMENT '背景图路径',
  font_size INT NOT NULL DEFAULT 20 COMMENT '名字大小',
  show_name TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否显示名字',
  avatar_size INT NOT NULL DEFAULT 48 COMMENT '头像大小(px)',
  phone_size INT NOT NULL DEFAULT 12 COMMENT '号码大小(px)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (id),
  UNIQUE KEY uk_dial_pages_slug (slug),
  KEY idx_dial_pages_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='拨号页';

-- 号码表
CREATE TABLE IF NOT EXISTS contacts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  dial_page_id INT UNSIGNED NOT NULL COMMENT '所属拨号页',
  name VARCHAR(100) NOT NULL COMMENT '名称',
  phone VARCHAR(32) NOT NULL COMMENT '手机号',
  avatar VARCHAR(255) NOT NULL DEFAULT '' COMMENT '头像路径',
  bg_color VARCHAR(16) NOT NULL DEFAULT '' COMMENT '卡片背景色',
  font_size INT DEFAULT NULL COMMENT '自定义字号（为空则用页面默认）',
  sort_order INT NOT NULL DEFAULT 0 COMMENT '排序',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (id),
  KEY idx_contacts_page (dial_page_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='号码';
