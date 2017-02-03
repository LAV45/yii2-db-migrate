yii2-db-migrate
===============

Это расширение поможет вам избежать конфликтов в вашей SQL db при создании foreign key.

```php
use lav45\db\MainMigration;

class m000000_000000_init extends MainMigration
{
    public function table_news()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            // ...
        ]);
        
        $this->addForeignKey('news', 'author_id', 'user', 'id', 'SET NULL');
    }
    
    public function table_news_tag()
    {
        $this->createTable('news_tag', [
            'news_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('news_tag', ['news_id', 'tag_id']);

        $this->addForeignKey('news_tag', 'news_id', 'news', 'id');
        $this->addForeignKey('news_tag', 'tag_id', 'tag', 'id');
    }

    public function table_tag()
    {
        $this->createTable('tag', [
            'id' => $this->primaryKey(),
            // ...
        ]);
    }

    public function table_user()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            // ...
        ]);
    }
}
```

```shell
~$ php yii migrate/up
Yii Migration Tool (based on Yii v2.0.7-dev)

Creating migration history table "migration"...Done.
Total 1 new migrations to be applied:
        m000000_000000_init

*** applying m000000_000000_init
    > create table user ... done (time: 0.072s)
    > create table news ... done (time: 0.064s)
    > add foreign key news_author_id_fkey: news (author_id) references user (id) ... done (time: 0.001s)
    > create table news_tag ... done (time: 0.001s)
    > add primary key news_tag_pk on news_tag (news_id,tag_id) ... done (time: 0.086s)
    > add foreign key news_tag_news_id_fkey: news_tag (news_id) references news (id) ... done (time: 0.001s)
    > create table tag ... done (time: 0.066s)
    > add foreign key news_tag_tag_id_fkey: news_tag (tag_id) references tag (id) ... done (time: 0.001s)
*** applied m000000_000000_init (time: 0.292s)


1 migrations were applied.

Migrated up successfully.
```