# Thiết Kế Database Cho Blog Với Laravel 12 & Inertia.js

## 1. Mô Hình Quan Hệ Database (ERD)
Hệ thống blog sẽ sử dụng đầy đủ các mối quan hệ trong Laravel:
- `hasOne`
- `hasMany`
- `belongsTo`
- `belongsToMany`
- `morphOne`, `morphMany`
- `morphedByMany`, `morphToMany`

### 1.1. Các Bảng Chính
#### **Bảng `users`** (Người dùng)
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('profile_image')->nullable();
    $table->enum('status', ['active', 'banned', 'pending'])->default('active');
    $table->timestamp('last_login_at')->nullable();
    $table->timestamps();
});
```
#### **Bảng `posts`** (Bài viết)
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('excerpt')->nullable();
    $table->longText('content');
    $table->integer('view_count')->default(0);
    $table->boolean('is_published')->default(false);
    $table->timestamps();
    $table->index(['slug', 'created_at']);
});
```
#### **Bảng `categories`** (Danh mục)
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
    $table->string('icon')->nullable();
    $table->integer('order')->default(0);
    $table->timestamps();
    $table->index('slug');
});
```
#### **Bảng `tags`** (Thẻ bài viết)
```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('color')->nullable();
    $table->timestamps();
});
```
#### **Bảng `post_tag`** (Quan hệ nhiều-nhiều giữa bài viết và thẻ)
```php
Schema::create('post_tag', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');
    $table->primary(['post_id', 'tag_id']);
});
```
#### **Bảng `comments`** (Bình luận)
```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('content');
    $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
    $table->enum('status', ['approved', 'pending', 'spam'])->default('pending');
    $table->timestamps();
});
```
#### **Bảng `media`** (Đa phương tiện - Morph)
```php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->string('file_path');
    $table->string('file_type');
    $table->integer('width')->nullable();
    $table->integer('height')->nullable();
    $table->string('alt_text')->nullable();
    $table->morphs('mediable');
    $table->timestamps();
});
```

## 2. Các Model & Quan Hệ
```php
class Post extends Model {
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}

class Media extends Model {
    public function mediable() {
        return $this->morphTo();
    }
}
```

## 3. Tối Ưu Hiệu Suất
### 3.1. **Tối ưu về Index**
- **Thêm index vào các trường truy vấn thường xuyên** như `slug`, `email`, `title`, `created_at`, `updated_at`.
- **Index cho bảng nhiều-nhiều**: Composite index (`post_id`, `tag_id`).

### 3.2. **Cải thiện cấu trúc bảng**
- **Users**: Thêm `status`, `profile_image`, `last_login_at`.
- **Posts**: Thêm `excerpt`, `view_count`, `is_published`.
- **Categories**: Thêm `icon`, `order`.
- **Tags**: Thêm `color`.
- **Comments**: Thêm `parent_id`, `status`.
- **Media**: Thêm `file_type`, `width`, `height`, `alt_text`.

### 3.3. **Tối ưu Cache**
- Cache bài viết phổ biến với Redis.
- Dùng queue để xử lý thông báo và logs.

## 4. Các Tính Năng Từ Cơ Bản Đến Nâng Cao
### **Cơ bản**
- CRUD bài viết, danh mục, thẻ.
- Đăng ký, đăng nhập, phân quyền.
- Bình luận dạng cây.
- Like/Dislike bài viết.

### **Nâng cao**
- **Tìm kiếm nâng cao** (Full-text search, Elasticsearch).
- **Cache bài viết phổ biến** để giảm tải DB.
- **Hệ thống bookmark** giúp người dùng lưu bài viết yêu thích.
- **Hệ thống nháp (Drafts)** cho phép lưu bài trước khi xuất bản.
- **Quản lý quyền nâng cao**: Kết hợp RBAC & PBAC.
- **Thông báo real-time** khi có bình luận mới.

## 5. Đảm Bảo Chuẩn 3NF (Third Normal Form)
- Không có dữ liệu dư thừa.
- Bảng được chuẩn hóa để đảm bảo tính toàn vẹn.
- Foreign key với `ON DELETE CASCADE` để đảm bảo đồng nhất.

---
Đây là thiết kế database tối ưu cho hệ thống blog. Nếu bạn có yêu cầu nào khác, hãy phản hồi nhé! 🚀

