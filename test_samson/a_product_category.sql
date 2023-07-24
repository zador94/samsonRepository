create table test_samson.a_product_category
(
    product  int null,
    category int null,
    constraint a_product_category_ibfk_1
        foreign key (product) references test_samson.a_product (id),
    constraint a_product_category_ibfk_2
        foreign key (category) references test_samson.a_category (id)
);

create index category
    on test_samson.a_product_category (category);

create index product
    on test_samson.a_product_category (product);

