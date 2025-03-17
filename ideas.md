# Blog Design - Laravel 12 + React + Inertia.js

## 1. Phân Tích Database

### **1.1. Các bảng chính**

- **Users**: Quản lý người dùng
- **Posts**: Bài viết
- **Categories**: Danh mục bài viết
- **Tags**: Nhãn bài viết
- **Comments**: Bình luận
- **Post_Tag**: Bảng trung gian giữa Posts và Tags
- **Post_Meta**: Thông tin mở rộng của bài viết (dùng polymorphic)
- **Likes**: Lượt thích bài viết hoặc bình luận (dùng polymorphic)
- **Media**: Quản lý ảnh/video đính kèm (dùng polymorphic)

### **1.2. Quan hệ giữa các bảng**

- **Users - Posts**: Một user có nhiều bài viết (hasMany)
- **Users - Comments**: Một user có nhiều bình luận (hasMany)
- **Posts - Categories**: Một bài viết thuộc về một danh mục (belongsTo)
- **Categories - Posts**: Một danh mục có nhiều bài viết (hasMany)
- **Categories - Categories**: Hỗ trợ danh mục cha-con (parent_id, hasMany & belongsTo)
- **Posts - Tags**: Many-to-Many (belongsToMany)
- **Posts - Comments**: Một bài viết có nhiều bình luận (hasMany)
- **Posts - Media**: Một bài viết có nhiều media (morphMany)
- **Posts - Likes**: Một bài viết có nhiều lượt thích (morphMany)
- **Comments - Likes**: Một bình luận có nhiều lượt thích (morphMany)
- **Posts - Post_Meta**: Một bài viết có nhiều meta (morphMany)

---

## 2. Các Tính Năng Mở Rộng

1. **Phân quyền**: User Role & Permission (RBAC & PBAC)
2. **Cache**: Sử dụng Redis hoặc Database Cache
3. **SEO**: Custom slug, meta description, OpenGraph
4. **Sắp xếp & tìm kiếm**: Full-text search với MySQL hoặc MeiliSearch
5. **Báo cáo vi phạm**: User có thể report bài viết, bình luận
6. **Bookmark**: Lưu bài viết yêu thích
7. **Theo dõi**: User có thể theo dõi tác giả
8. **API**: Xây dựng API cho frontend và mobile

---

## 3. Migrations

### **3.1. Tạo bảng Users**

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->timestamps();
});
```

### **3.2. Tạo bảng Categories**

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->foreignId('parent_id')->nullable()->constrained('categories');
    $table->timestamps();
});
```

### **3.3. Tạo bảng Posts**

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('content');
    $table->string('slug')->unique();
    $table->timestamps();
});
```

### **3.4. Tạo bảng Tags & Post_Tag**

```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->timestamps();
});

Schema::create('post_tag', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');
});
```

---

## 4. Models & Relationships

### **4.1. User Model**

```php
class User extends Model {
    use HasFactory;

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
```

### **4.2. Post Model**

```php
class Post extends Model {
    use HasFactory;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
```

### **4.3. Category Model**

```php
class Category extends Model {
    use HasFactory;

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
```

### **4.4. Tag Model**

```php
class Tag extends Model {
    use HasFactory;

    public function posts() {
        return $this->belongsToMany(Post::class);
    }
}
```

---

## 5. Kết Luận

- Blog này sử dụng đầy đủ các quan hệ trong Laravel
- Hỗ trợ mở rộng dễ dàng với các tính năng nâng cao
- Thiết kế tối ưu cho SEO, cache, API

Bây giờ bạn có thể triển khai tiếp frontend với React + Inertia.js. 🚀
