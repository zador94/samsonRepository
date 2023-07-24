create table test_samson.a_property
(
    product        int          null,
    property_name  varchar(255) null,
    property_value varchar(255) null,
    constraint a_property_ibfk_1
        foreign key (product) references test_samson.a_product (id)
);

create index product
    on test_samson.a_property (product);

