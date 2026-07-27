<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * MySQL port of prisma/schema.prisma. Column names intentionally match the
 * Prisma schema (camelCase) so business logic maps 1:1 to the original app.
 */
class InitSchema extends Migration
{
    public function up()
    {
        // categories
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 191],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 191],
            'description' => ['type' => 'TEXT', 'null' => true],
            'image'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'parentId'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sortOrder'   => ['type' => 'INT', 'default' => 0],
            'isActive'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'isFeatured'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('parentId', 'categories', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('categories');

        // products
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 191],
            'slug'                => ['type' => 'VARCHAR', 'constraint' => 191],
            'categoryId'          => ['type' => 'INT', 'unsigned' => true],
            'description'         => ['type' => 'TEXT'],
            'shortDescription'    => ['type' => 'TEXT'],
            'price'               => ['type' => 'DOUBLE'],
            'discountPrice'       => ['type' => 'DOUBLE', 'null' => true],
            'unit'                => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Kg'],
            'weightOptions'       => ['type' => 'TEXT', 'null' => true], // JSON array of strings
            'stockStatus'         => ['type' => 'ENUM', 'constraint' => ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'], 'default' => 'IN_STOCK'],
            'isFeatured'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'isPopular'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'isNewArrival'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'isBestSeller'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'isSeasonal'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'isFresh'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'isOrganic'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'onOffer'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'nutrition'           => ['type' => 'TEXT', 'null' => true],
            'storageInstructions' => ['type' => 'TEXT', 'null' => true],
            'origin'              => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'brand'               => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'seoTitle'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seoDescription'      => ['type' => 'TEXT', 'null' => true],
            'metaKeywords'        => ['type' => 'TEXT', 'null' => true],
            'views'               => ['type' => 'INT', 'default' => 0],
            'createdAt'           => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('categoryId');
        $this->forge->addKey('isFeatured');
        $this->forge->addKey('createdAt');
        $this->forge->addForeignKey('categoryId', 'categories', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('products');

        // product_images
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'url'       => ['type' => 'VARCHAR', 'constraint' => 500],
            'alt'       => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'sortOrder' => ['type' => 'INT', 'default' => 0],
            'productId' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('productId');
        $this->forge->addForeignKey('productId', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_images');

        // banners
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'subtitle'  => ['type' => 'TEXT', 'null' => true],
            'image'     => ['type' => 'VARCHAR', 'constraint' => 500],
            'ctaText'   => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'Shop Now'],
            'ctaLink'   => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => '/products'],
            'sortOrder' => ['type' => 'INT', 'default' => 0],
            'isActive'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'createdAt' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('banners');

        // enquiries
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'customerName'    => ['type' => 'VARCHAR', 'constraint' => 191],
            'companyName'     => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 191],
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'location'        => ['type' => 'VARCHAR', 'constraint' => 191],
            'deliveryAddress' => ['type' => 'TEXT'],
            'productId'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'productName'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'quantity'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'preferredUnit'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'preferredDate'   => ['type' => 'DATETIME', 'null' => true],
            'notes'           => ['type' => 'TEXT', 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['NEW', 'CONTACTED', 'CLOSED'], 'default' => 'NEW'],
            'createdAt'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('createdAt');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('productId', 'products', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('enquiries');

        // newsletter_subscribers
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'     => ['type' => 'VARCHAR', 'constraint' => 191],
            'createdAt' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('newsletter_subscribers');

        // admin_users
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 191],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 191],
            'passwordHash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'createdAt'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('admin_users');

        // settings
        $this->forge->addField([
            'key'   => ['type' => 'VARCHAR', 'constraint' => 191],
            'value' => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('key', true);
        $this->forge->createTable('settings');

        // testimonials
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 191],
            'role'      => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true],
            'avatar'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'rating'    => ['type' => 'INT', 'default' => 5],
            'content'   => ['type' => 'TEXT'],
            'isActive'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sortOrder' => ['type' => 'INT', 'default' => 0],
            'createdAt' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('testimonials');
    }

    public function down()
    {
        $this->forge->dropTable('testimonials', true);
        $this->forge->dropTable('settings', true);
        $this->forge->dropTable('admin_users', true);
        $this->forge->dropTable('newsletter_subscribers', true);
        $this->forge->dropTable('enquiries', true);
        $this->forge->dropTable('banners', true);
        $this->forge->dropTable('product_images', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('categories', true);
    }
}
