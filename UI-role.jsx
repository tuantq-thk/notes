import { useState } from "react";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Table, TableHead, TableRow, TableCell, TableBody } from "@/components/ui/table";

const roles = [
  { id: 1, name: "Admin", permissions: ["create_post", "edit_post", "delete_post"] },
  { id: 2, name: "Editor", permissions: ["create_post", "edit_post"] },
  { id: 3, name: "User", permissions: ["view_post"] }
];

const permissions = ["create_post", "edit_post", "delete_post", "view_post"];

export default function RolePermissionUI() {
  const [roleData, setRoleData] = useState(roles);

  const handlePermissionChange = (roleId, permission) => {
    setRoleData((prev) =>
      prev.map((role) =>
        role.id === roleId
          ? {
              ...role,
              permissions: role.permissions.includes(permission)
                ? role.permissions.filter((p) => p !== permission)
                : [...role.permissions, permission]
            }
          : role
      )
    );
  };

  return (
    <div className="p-6">
      <h2 className="text-xl font-bold mb-4">Quản lý Role-Permission</h2>
      <Card>
        <CardContent>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Role</TableCell>
                {permissions.map((perm) => (
                  <TableCell key={perm}>{perm}</TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {roleData.map((role) => (
                <TableRow key={role.id}>
                  <TableCell>{role.name}</TableCell>
                  {permissions.map((perm) => (
                    <TableCell key={perm}>
                      <Checkbox
                        checked={role.permissions.includes(perm)}
                        onCheckedChange={() => handlePermissionChange(role.id, perm)}
                      />
                    </TableCell>
                  ))}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
      <Button className="mt-4">Lưu thay đổi</Button>
    </div>
  );
}


// 3. Cách kiểm tra quyền trên Frontend
// Dù Backend kiểm tra quyền, bạn vẫn nên kiểm tra quyền trên Frontend để ẩn hoặc vô hiệu hóa các nút không cần thiết.

// 🔹 Cách 1: Kiểm tra quyền trước khi hiển thị UI
function CanAccess({ permission, children }) {
    const { permissions } = useContext(AuthContext);
    return permissions.includes(permission) ? children : null;
}

// Dùng component kiểm tra quyền
<CanAccess permission="create_post">
    <button>Thêm bài viết</button>
</CanAccess>
// 🔹 Cách 2: Chặn request nếu không có quyền
// Dùng Interceptor của Axios để kiểm tra quyền trước khi gửi request.

axios.interceptors.request.use((config) => {
    if (!userPermissions.includes(config.permission)) {
        return Promise.reject({ message: "Bạn không có quyền" });
    }
    return config;
});
// ➡️ Lợi ích: Người dùng không thể nhấn vào nút nếu họ không có quyền, giảm số request sai lên server.