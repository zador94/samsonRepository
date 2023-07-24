create table test_samson.a_price
(
    product    int            null,
    price_type varchar(255)   null,
    price      decimal(10, 2) null,
    constraint a_price_ibfk_1
        foreign key (product) references test_samson.a_product (id)
);

create index product
    on test_samson.a_price (product);

