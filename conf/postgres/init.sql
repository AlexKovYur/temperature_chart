CREATE TABLE IF NOT EXISTS temperatures
(
    id serial constraint temperatures_pk primary key,
    date  TIMESTAMP not null,
    value int       not null
);

CREATE INDEX idx_temperatures_date ON temperatures(date);