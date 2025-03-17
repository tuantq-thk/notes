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
- **Follows**: Hệ thống theo dõi user
- **Reports**: Hệ thống báo cáo vi phạm
- **Bookmarks**: Hệ thống lưu bài viết yêu thích

### **1.2. Quan hệ giữa các bảng**

- **Users - Posts**: Một user có nhiều bài viết (hasMany)
- **Users - Comments**: Một user có nhiều bình luận (hasMany)
- **Users - Likes**: Một user có nhiều lượt thích (hasMany)
- **Users - Follows**: Một user có thể theo dõi nhiều người và bị nhiều người theo dõi (belongsToMany)
- **Users - Reports**: Một user có thể báo cáo nhiều bài viết hoặc bình luận (hasMany)
- **Users - Bookmarks**: Một user có thể lưu nhiều bài viết yêu thích (belongsToMany)
- **Posts - Categories**: Một bài viết thuộc về một danh mục (belongsTo)
- **Categories - Posts**: Một danh mục có nhiều bài viết (hasMany)
- **Categories - Categories**: Hỗ trợ danh mục cha-con (parent_id, hasMany & belongsTo)
- **Posts - Tags**: Many-to-Many (belongsToMany)
- **Posts - Comments**: Một bài viết có nhiều bình luận (hasMany)
- **Posts - Media**: Một bài viết có nhiều media (morphMany)
- **Posts - Likes**: Một bài viết có nhiều lượt thích (morphMany)
- **Posts - Reports**: Một bài viết có nhiều báo cáo vi phạm (hasMany)
- **Posts - Bookmarks**: Một bài viết có thể được nhiều người lưu (belongsToMany)
- **Comments - Likes**: Một bình luận có nhiều lượt thích (morphMany)
- **Comments - Reports**: Một bình luận có nhiều báo cáo vi phạm (hasMany)
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

### **3.5. Tạo bảng Media**

```php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->string('url');
    $table->morphs('mediable');
    $table->timestamps();
});
```

### **3.6. Tạo bảng Likes**

```php
Schema::create('likes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->morphs('likeable');
    $table->timestamps();
});
```

### **3.7. Tạo bảng Post_Meta**

```php
Schema::create('post_meta', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->string('key');
    $table->text('value');
    $table->timestamps();
});
```

### **3.8. Tạo bảng Bookmarks**

```php
Schema::create('bookmarks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

---

## 4. Models & Relationships

### **4.5. Media Model**

```php
class Media extends Model {
    use HasFactory;

    public function mediable() {
        return $this->morphTo();
    }
}
```

### **4.6. Like Model**

```php
class Like extends Model {
    use HasFactory;

    public function likeable() {
        return $this->morphTo();
    }
}
```

### **4.7. Post Model (Cập nhật)**

```php
class Post extends Model {
    use HasFactory;

    public function media() {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function likes() {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function meta() {
        return $this->hasMany(PostMeta::class);
    }
}
```

### **4.8. User Model (Cập nhật)**

```php
class User extends Authenticatable {
    use HasFactory;

    public function bookmarks() {
        return $this->belongsToMany(Post::class, 'bookmarks');
    }

    public function following() {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }
}
```

---

## 5. Kết Luận

- Database đảm bảo tính chặt chẽ, ràng buộc khóa ngoại, chuẩn **3F**.
- Hỗ trợ **tất cả các loại quan hệ** trong Laravel.
- Thiết kế **mở rộng dễ dàng**, phù hợp cho ứng dụng lớn.
- Tích hợp **các tính năng nâng cao** như phân quyền, cache, API.

Bây giờ bạn có thể triển khai tiếp frontend với React + Inertia.js. 🚀