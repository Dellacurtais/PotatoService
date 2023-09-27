CREATE TABLE user (
    id           bigint auto_increment primary key,
    username     varchar(255) not null,
    password     varchar(255) not null,
    email        varchar(255) not null,
    enabled      tinyint not null,
    last_login   datetime null,
    status       enum ('ACTIVE', 'INACTIVE', 'LOCKED', 'NEED_CONFIRMATION') default 'NEED_CONFIRMATION' not null,
    created_at datetime default CURRENT_TIMESTAMP not null,
    updated_at datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint email unique (email),
    constraint username unique (username)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;