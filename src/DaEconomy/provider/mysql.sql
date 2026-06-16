-- #!mysql
-- #{ economy

-- #  { init
CREATE TABLE IF NOT EXISTS economy (
    xuid VARCHAR(36) PRIMARY KEY,
    balance INT NOT NULL
);
-- #  }

-- #  { get
SELECT balance FROM economy WHERE xuid = :xuid;
-- #  }

-- #  { create
INSERT IGNORE INTO economy (xuid, balance) VALUES (:xuid, :balance);
-- #  }

-- #  { set
UPDATE economy SET balance = :balance WHERE xuid = :xuid;
-- #  }

-- #  { add
UPDATE economy SET balance = balance + :amount WHERE xuid = :xuid;
-- #  }

-- #  { reduce
UPDATE economy SET balance = balance - :amount WHERE xuid = :xuid;
-- #  }

-- #  { delete
DELETE FROM economy WHERE xuid = :xuid;
-- #  }

-- #}
