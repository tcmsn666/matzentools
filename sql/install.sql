CREATE DATABASE IF NOT EXISTS matzentools CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE matzentools;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS object_qm_documents;
DROP TABLE IF EXISTS object_document_checks;
DROP TABLE IF EXISTS qm_document_versions;
DROP TABLE IF EXISTS qm_documents;
DROP TABLE IF EXISTS objects;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS user_menu_permissions;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(120) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_key VARCHAR(80) NOT NULL UNIQUE,
  title VARCHAR(160) NOT NULL,
  module_path VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NULL,
  title VARCHAR(160) NOT NULL,
  module_key VARCHAR(80) NULL,
  sort_order INT NOT NULL DEFAULT 100,
  icon VARCHAR(50) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  admin_only TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL,
  CONSTRAINT fk_menu_module FOREIGN KEY (module_key) REFERENCES modules(module_key) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_menu_permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  menu_item_id INT NOT NULL,
  can_access TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_menu (user_id, menu_item_id),
  CONSTRAINT fk_perm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_perm_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_username_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE objects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  object_name VARCHAR(160) NOT NULL,
  address VARCHAR(255) NULL,
  status ENUM('aktiv','inaktiv') NOT NULL DEFAULT 'aktiv',
  start_date DATE NULL,
  end_date DATE NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE qm_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_number VARCHAR(50) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  document_type VARCHAR(80) NULL,
  status ENUM('aktiv','ersetzt','aus_qm_entfernt') NOT NULL DEFAULT 'aktiv',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE qm_document_versions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_id INT NOT NULL,
  document_date DATE NOT NULL,
  version_label VARCHAR(100) NULL,
  change_type ENUM('neu_erstellt','aktualisiert','aus_qm_entfernt') NOT NULL DEFAULT 'aktualisiert',
  is_current TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_version_document FOREIGN KEY (document_id) REFERENCES qm_documents(id) ON DELETE CASCADE,
  INDEX idx_document_current (document_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE object_document_checks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  object_id INT NOT NULL UNIQUE,
  work_instruction_date DATE NULL,
  work_instruction_checked_at DATE NULL,
  gbu_date DATE NULL,
  gbu_checked_at DATE NULL,
  notes TEXT NULL,
  updated_by INT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_check_object FOREIGN KEY (object_id) REFERENCES objects(id) ON DELETE CASCADE,
  CONSTRAINT fk_check_user FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE object_qm_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  object_id INT NOT NULL,
  document_id INT NOT NULL,
  document_version_id INT NOT NULL,
  assigned_at DATE NULL,
  last_checked_at DATE NULL,
  updated_by INT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_object_document (object_id, document_id),
  CONSTRAINT fk_oqd_object FOREIGN KEY (object_id) REFERENCES objects(id) ON DELETE CASCADE,
  CONSTRAINT fk_oqd_document FOREIGN KEY (document_id) REFERENCES qm_documents(id) ON DELETE CASCADE,
  CONSTRAINT fk_oqd_version FOREIGN KEY (document_version_id) REFERENCES qm_document_versions(id) ON DELETE RESTRICT,
  CONSTRAINT fk_oqd_user FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //
CREATE TRIGGER trg_qm_versions_one_current_before_insert
BEFORE INSERT ON qm_document_versions
FOR EACH ROW
BEGIN
  IF NEW.is_current = 1 THEN
    UPDATE qm_document_versions SET is_current = 0 WHERE document_id = NEW.document_id;
  END IF;
END//
CREATE TRIGGER trg_qm_versions_one_current_before_update
BEFORE UPDATE ON qm_document_versions
FOR EACH ROW
BEGIN
  IF NEW.is_current = 1 THEN
    UPDATE qm_document_versions SET is_current = 0 WHERE document_id = NEW.document_id AND id <> NEW.id;
  END IF;
END//
DELIMITER ;

INSERT INTO users (id, username, password_hash, display_name, role, is_active, must_change_password) VALUES
(1, 'admin', '$2y$12$ikSH2YcQQd.vUwmqZ1Tz/OoJ2I2HxWA3rQ2rDmj3MngKdznnhbzL2', 'Administrator', 'admin', 1, 1);

INSERT INTO modules (id, module_key, title, module_path, is_active) VALUES
(1,'dashboard','Dashboard','dashboard/index.php',1),
(2,'admin_users','Benutzerverwaltung','admin/users.php',1),
(3,'admin_menu','Menüverwaltung','admin/menu.php',1),
(4,'admin_modules','Modulverwaltung','admin/modules.php',1),
(5,'admin_permissions','Rechteverwaltung','admin/permissions.php',1),
(6,'objects','Objekte verwalten','objects_qm/objects.php',1),
(7,'qm_documents','QM-Dokumente','objects_qm/qm_documents.php',1),
(8,'document_control','Dokumentenkontrolle','objects_qm/document_control.php',1),
(9,'outdated_documents','Veraltete Objektordner','objects_qm/outdated_documents.php',1);

INSERT INTO menu_items (id,parent_id,title,module_key,sort_order,icon,is_active,admin_only) VALUES
(1,NULL,'Dashboard','dashboard',10,'home',1,0),
(2,NULL,'Admin',NULL,20,'admin',1,1),
(3,2,'Benutzerverwaltung','admin_users',10,'users',1,1),
(4,2,'Menüverwaltung','admin_menu',20,'menu',1,1),
(5,2,'Modulverwaltung','admin_modules',30,'module',1,1),
(6,2,'Rechteverwaltung','admin_permissions',40,'lock',1,1),
(7,NULL,'Objekte / QM',NULL,30,'folder',1,0),
(8,7,'Objekte verwalten','objects',10,'object',1,0),
(9,7,'QM-Dokumente','qm_documents',20,'doc',1,0),
(10,7,'Dokumentenkontrolle','document_control',30,'check',1,0),
(11,7,'Veraltete Objektordner','outdated_documents',40,'warning',1,0);

INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access)
SELECT 1, id, 1 FROM menu_items;

INSERT INTO objects (id, object_name, address, status, start_date, end_date, notes) VALUES
(1,'Objekt Nordtor','Musterstraße 12, 24937 Flensburg','aktiv','2024-01-01',NULL,'Demoobjekt aktiv'),
(2,'Objekt Südhof','Beispielweg 5, 24941 Flensburg','aktiv','2023-06-01',NULL,'Demoobjekt mit veraltetem Dokument');

INSERT INTO qm_documents (id, document_number, title, document_type, status, notes) VALUES
(1,'QMV 19','Verhalten im Objekt','Verfahrensanweisung','aktiv',NULL),
(2,'QMA 03','Arbeitsanweisung Empfang','Arbeitsanweisung','aktiv',NULL),
(3,'QMF 07','Kontrollformular Rundgang','Formular','aus_qm_entfernt','Aus QM entfernt als Demo');

INSERT INTO qm_document_versions (id, document_id, document_date, version_label, change_type, is_current, notes) VALUES
(1,1,'2024-01-15','Version 1','neu_erstellt',0,NULL),
(2,1,'2025-04-20','Version 2','aktualisiert',1,NULL),
(3,2,'2024-03-01','Version 1','neu_erstellt',0,NULL),
(4,2,'2025-09-10','Version 2','aktualisiert',1,NULL),
(5,3,'2023-11-01','Version 1','neu_erstellt',0,NULL),
(6,3,'2025-01-01','entfernt','aus_qm_entfernt',1,'Nicht mehr Bestandteil des QM');

INSERT INTO object_document_checks (object_id, work_instruction_date, work_instruction_checked_at, gbu_date, gbu_checked_at, notes, updated_by) VALUES
(1,'2025-04-20','2026-01-15','2025-02-01','2026-01-15','Alles aktuell',1),
(2,'2024-03-01','2026-01-10','2024-02-01','2026-01-10','Enthält absichtlich veraltete Dokumente',1);

INSERT INTO object_qm_documents (object_id, document_id, document_version_id, assigned_at, last_checked_at, updated_by, notes) VALUES
(1,1,2,'2025-04-25','2026-01-15',1,'Aktuelle Version'),
(1,2,4,'2025-09-15','2026-01-15',1,'Aktuelle Version'),
(2,1,1,'2024-02-01','2026-01-10',1,'Veraltete Version als Demo'),
(2,3,5,'2023-12-01','2026-01-10',1,'Aus QM entfernt als Demo');
