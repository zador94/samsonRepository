create table test_samson.a_category
(
    id              int auto_increment
        primary key,
    code            int          null,
    name            varchar(255) null,
    parent_category int          null,
    constraint a_category_ibfk_1
        foreign key (parent_category) references test_samson.a_category (id)
);

create index parent_category
    on test_samson.a_category (parent_category);

