# 快拨通讯录

一个「快速拨打电话」的网页应用：用户用手机号注册登录后，可创建多个拨号页，每个拨号页生成唯一链接与二维码用于分享；访问者打开链接即可看到通讯录，点击头像一键拨出电话。

- 前端：Vue 3 + TypeScript + Vite + TailwindCSS + Vue Router
- 后端：PHP 8（原生）+ MySQL
- 数据库：MySQL（表结构与建库在首次启动时自动完成，也可手动导入）

## 目录结构

```
openWeChat/
├── backend/                 # 后端（PHP）
│   ├── config.php           # 数据库连接配置
│   ├── database/
│   │   └── schema.sql       # MySQL 建表脚本
│   ├── public/
│   │   ├── index.php        # 单一入口（路由 + 静态文件）
│   │   └── uploads/         # 头像 / 背景图上传目录（自动创建）
│   └── src/
│       ├── Database.php     # PDO 连接与建库建表
│       ├── Auth.php         # 认证与 Token
│       ├── Response.php     # 统一响应封装
│       └── controllers/     # 控制器
├── frontend/                # 前端（Vue 3 SPA）
│   ├── src/                 # 页面、组件、路由、API 封装
│   └── vite.config.ts       # 开发代理（/api、/uploads → 后端）
└── README.md
```

## 环境要求

| 依赖 | 版本 | 说明 |
|------|------|------|
| Node.js | >= 18 | 前端构建与运行 |
| npm | 随 Node.js | 依赖安装 |
| PHP | >= 8.0 | 需启用 `pdo_mysql` 扩展 |
| MySQL | 5.7+（推荐 8.0） | 数据库服务 |

## 首次部署步骤

### 1. 安装并启动 MySQL

macOS（Homebrew）：

```bash
brew install mysql
brew services start mysql
```

安装后设置一个可用的数据库账号（默认使用 `root`，密码为空）。如果你的 `root` 有密码，请在 `backend/config.php` 中修改。

### 2. 配置后端数据库连接

编辑 [backend/config.php](backend/config.php)，按需修改以下配置（也可通过环境变量 `DB_HOST`、`DB_PORT`、`DB_NAME`、`DB_USER`、`DB_PASS` 覆盖）：

```php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'quickdial',   // 数据库名（需先创建）
    'user' => 'root',
    'pass' => '',            // MySQL 密码
    'charset' => 'utf8mb4',
];
```

> 数据库需**提前创建**（程序只会在连接后自动建表，不会自动建库）。可用宝塔「数据库」面板创建，或执行下面的命令。

建库并导入表结构（两种方式二选一）：

方式一：手动建库建表

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS quickdial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p quickdial < backend/database/schema.sql
```

方式二：只建库，首次访问后端时自动建表

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS quickdial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 3. 启动后端

```bash
cd backend
php -S localhost:8000 -t public public/index.php
```

> 注意：`public/index.php` 是单入口路由脚本，必须作为路由器传入（上面的 `public/index.php` 参数），否则 `/api/*` 会返回 404。

确认 PHP 已启用 `pdo_mysql`（没有则安装/启用后再启动）：

```bash
php -m | grep pdo_mysql
```

启动后可访问健康检查（返回 JSON）：

```
http://localhost:8000/api/auth/me
```

### 4. 启动前端

开发模式（另开一个终端）：

```bash
cd frontend
npm install
npm run dev
```

打开终端输出的地址（默认 `http://localhost:5173`）。前端开发服务器已把 `/api` 与 `/uploads` 代理到 `http://localhost:8000`，无需额外配置。

生产构建：

```bash
cd frontend
npm install
npm run build
```

将 `frontend/dist` 放到网站根目录交给任意静态服务器（如 Nginx）托管。前端已采用 **hash 路由 + `/backend/public/index.php?r=` 转发**方式，因此无需配置 SPA 伪静态，也无需反向代理 `/api`、`/uploads`。

### 部署到宝塔（零配置，推荐）

1. 把 `frontend/dist` 里的**所有文件**上传到网站根目录（`index.html` 直接落在根目录）。
2. 在网站根目录创建 `backend` 目录，把整个 `backend/` 上传进去，保持 `backend/public/index.php`、`backend/src/`、`backend/config.php`、`backend/database/schema.sql` 结构不变。
3. 确保该站点启用了 **PHP**（版本 >= 8.0，且含 `pdo_mysql` 扩展）。宝塔的 PHP-FPM 会直接执行 `/backend/public/index.php`。
4. 确保 `backend/public/uploads` 目录可写（头像 / 背景图上传需要）。
5. 在 `backend/config.php` 里填好 MySQL 账号密码（宝塔 MySQL 通常是 `127.0.0.1:3306` + 你创建的库名与账号）。

完成后访问 `http://你的域名/`，前端会通过 `/backend/public/index.php?r=/api/...` 直接请求 PHP 后端，无需 `php -S` 进程或反向代理。

### 其它部署方式（无 PHP-FPM 时）

如果服务器没有 PHP-FPM（例如纯静态托管 + 单独跑 PHP），可启动后端进程并配置反向代理：

启动后端进程（常驻）：

```bash
cd backend
nohup php -S 127.0.0.1:8000 -t public public/index.php >/tmp/quickcall.log 2>&1 &
```

Nginx 反向代理配置（加入站点 `server` 块，且要放在 SPA 的 `try_files` 回退规则之前）：

```nginx
location /api/ {
    proxy_pass http://127.0.0.1:8000;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}

location /uploads/ {
    proxy_pass http://127.0.0.1:8000;
}
```

Apache（需启用 `mod_proxy`）可参考：

```apache
ProxyPass /api/ http://127.0.0.1:8000/api/
ProxyPassReverse /api/ http://127.0.0.1:8000/api/
ProxyPass /uploads/ http://127.0.0.1:8000/uploads/
ProxyPassReverse /uploads/ http://127.0.0.1:8000/uploads/
```

## 首次使用流程

1. 打开前端地址，进入注册页，用「手机号 + 密码 + 重复密码」注册（注册成功自动登录）。
2. 登录后进入管理页，点击「新建拨号页」。
3. 在编辑页添加号码，配置名称、手机号、头像、背景色 / 字号等。
4. 点击「分享」获取拨号页链接与二维码，或复制单个号码的分享链接。
5. 访客用手机打开链接即可看到通讯录，点击头像直接拨号；打开单号码链接会自动拨号。

## 说明与注意事项

- **上传目录**：头像与背景图保存在 `backend/public/uploads`，首次上传时会自动创建，请确保该目录对 PHP 进程可写。
- **自动拨号**：拨号通过 `tel:` 协议触发，在手机浏览器上会拉起系统拨号盘；桌面端行为取决于系统是否配置了电话处理程序。
- **头像兜底**：未上传头像时，使用「名称最后两个字 + 由手机号计算出的固定背景色」作为头像。
- **列数自适应**：拨号页根据屏幕宽度与字号在 1～3 列之间自动调整。
- **登录态**：登录 / 注册后返回 Bearer Token，前端存储在 `localStorage`。
