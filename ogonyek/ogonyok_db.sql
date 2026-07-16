SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `ogonyok_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `guests_count` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `guest_name`, `phone`, `email`, `booking_date`, `booking_time`, `guests_count`, `comment`, `status`, `created_at`, `archived_at`) VALUES
(1, 3, 'Дмитрий Петров', '+7 (999) 555-44-33', 'dmitry@ogonyek.ru', '2026-06-07', '18:00:00', 4, 'У окна, если возможно', 'confirmed', '2026-06-07 17:55:14', '2026-07-13 06:45:48'),
(2, NULL, 'Ольга Павлова', '+7 (999) 888-22-11', 'olga@yandex.ru', '2026-06-07', '19:00:00', 2, 'Празднуем день рождения', 'pending', '2026-06-07 17:55:14', '2026-07-13 06:45:48'),
(3, 5, 'Иван Смирнов', '+7 (999) 222-33-44', 'ivan@ogonyek.ru', '2026-06-07', '20:00:00', 6, 'Нужен детский стульчик', 'confirmed', '2026-06-07 17:55:14', '2026-07-13 06:45:48'),
(4, NULL, 'Сергей Волков', '+7 (999) 333-77-88', 'sergey@yandex.ru', '2026-06-07', '15:00:00', 3, NULL, 'cancelled', '2026-06-07 17:55:14', '2026-07-13 06:45:48'),
(5, 4, 'Мария Сидорова', '+7 (999) 777-88-99', 'maria@ogonyek.ru', '2026-06-08', '19:00:00', 2, NULL, 'confirmed', '2026-06-07 17:55:14', '2026-07-13 06:45:48'),
(6, NULL, 'Анна Морозова', '+7 (999) 555-99-88', 'anna@mail.ru', '2026-06-09', '17:00:00', 5, 'Тихий столик', 'pending', '2026-06-07 17:55:14', '2026-07-13 06:45:48'),
(7, 2, 'Александр', '+7 (111) 111-11-11', 'user123@ogonyok.ru', '2026-07-12', '15:00:00', 3, '', 'pending', '2026-07-11 13:44:55', '2026-07-13 06:45:48'),
(8, 2, 'Александр', '+7 (111) 111-11-11', 'user123@ogonyok.ru', '2026-07-12', '16:00:00', 3, 'у окна', 'pending', '2026-07-11 13:45:37', '2026-07-13 06:45:48'),
(9, 8, 'Коля', '+7 (111) 111-11-11', 'kolya@user.com', '2026-07-13', '18:00:00', 3, 'у окна', 'cancelled', '2026-07-12 14:47:04', '2026-07-13 06:45:48'),
(10, 1, 'Администратор ресторана', '+7 (999) 111-22-33', 'admin@ogonyok.ru', '2026-07-13', '11:00:00', 2, '', 'pending', '2026-07-13 16:43:01', NULL),
(12, NULL, 'Иван', '+7 (912) 345-67-89', 'example@mail.com', '2026-07-13', '20:00:00', 2, 'детский стул', 'pending', '2026-07-13 16:53:10', NULL),
(13, 1, 'Администратор ресторана', '+7 (999) 111-22-33', 'admin@ogonyek.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 1', 'cancelled', '2026-07-13 17:01:24', NULL),
(14, 2, 'Александр', '+7 (111) 111-11-11', 'user123@ogonyek.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 2', 'confirmed', '2026-07-13 17:01:24', NULL),
(15, 3, 'Дмитрий Петров', '+7 (999) 555-44-33', 'dmitry@ogonyek.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 3', 'confirmed', '2026-07-13 17:01:24', NULL),
(16, 4, 'Мария Сидорова', '+7 (999) 777-88-99', 'maria@ogonyek.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 4', 'confirmed', '2026-07-13 17:01:24', NULL),
(17, 5, 'Иван Смирнов', '+7 (999) 222-33-44', 'ivan@ogonyek.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 5', 'confirmed', '2026-07-13 17:01:24', NULL),
(18, 6, 'Елена Кузнецова', '+7 (999) 111-55-66', 'elena@ogonyek.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 6', 'confirmed', '2026-07-13 17:01:24', NULL),
(19, NULL, 'Анна Кузнецова', '+7 (999) 000-00-01', 'anna.k@yandex.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 7', 'confirmed', '2026-07-13 17:01:24', NULL),
(20, NULL, 'Сергей Петров', '+7 (999) 000-00-02', 'sergey.p@yandex.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 8', 'confirmed', '2026-07-13 17:01:24', NULL),
(21, NULL, 'Ольга Иванова', '+7 (999) 000-00-03', 'olga.i@yandex.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 9', 'confirmed', '2026-07-13 17:01:24', NULL),
(22, NULL, 'Николай Соколов', '+7 (999) 000-00-04', 'nikolay.s@yandex.ru', '2026-07-13', '21:00:00', 2, 'Тест ограничения 10', 'confirmed', '2026-07-13 17:01:24', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(4, 4, 9, 1, '2026-07-11 14:44:09');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `deleted_at`) VALUES
(1, 'Стейки', 'steaks', 1, NULL),
(2, 'Бургеры', 'burgers', 2, NULL),
(3, 'Салаты', 'salads', 3, NULL),
(4, 'Напитки', 'drinks', 4, NULL),
(5, 'Горячие блюда', 'hot', 5, NULL),
(9, 'Test', 'test', 6, '2026-07-13 06:49:30');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `delivery_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `status`, `total_price`, `delivery_address`, `created_at`, `archived_at`) VALUES
(1, 3, 'completed', 3097.00, 'г. Ухта, ул. Ленина, д. 24, кв. 15', '2026-06-07 17:55:14', '2026-07-13 06:45:26'),
(2, 4, 'completed', 1379.00, 'г. Ухта, ул. Куратова, д. 6, кв. 89 (Примечание: Домофон не работает)', '2026-06-07 15:55:14', '2026-07-13 06:45:26'),
(3, 5, 'pending', 2489.00, 'г. Ухта, пр-кт Космонавтов, д. 12, кв. 4', '2026-06-07 16:55:14', '2026-07-13 06:45:26'),
(4, 5, 'completed', 4596.00, 'г. Ухта, пр-кт Космонавтов, д. 12, кв. 4', '2026-06-06 17:55:14', '2026-07-13 06:45:26'),
(5, 6, 'completed', 1049.00, 'г. Ухта, ул. Сенюкова, д. 8, кв. 112', '2026-06-06 14:55:14', '2026-07-13 06:45:26'),
(6, 3, 'completed', 2697.00, 'г. Ухта, ул. Ленина, д. 24, кв. 15', '2026-06-05 17:55:14', '2026-07-13 06:45:26'),
(7, 4, 'completed', 3897.00, 'г. Ухта, ул. Куратова, д. 6, кв. 89', '2026-06-04 17:55:14', '2026-07-13 06:45:26'),
(8, 5, 'cancelled', 1299.00, 'г. Ухта, ул. Юбилейная, д. 14, кв. 5', '2026-06-04 13:55:14', '2026-07-13 06:45:26'),
(9, 6, 'completed', 1698.00, 'г. Ухта, ул. Сенюкова, д. 8, кв. 112', '2026-06-03 17:55:14', '2026-07-13 06:45:26'),
(10, 3, 'completed', 5396.00, 'г. Ухта, ул. Ленина, д. 24, кв. 15', '2026-06-02 17:55:14', '2026-07-13 06:45:26'),
(11, 4, 'completed', 2398.00, 'г. Ухта, ул. Куратова, д. 6, кв. 89', '2026-06-01 17:55:14', '2026-07-13 06:45:26'),
(12, 1, 'pending', 1299.00, 'г. Ухта, ул. Ленина, д. 1, под. 12, кв./офис 123', '2026-07-11 12:56:55', '2026-07-13 06:45:26'),
(13, 3, 'pending', 1299.00, 'г. Ухта, ул. ул. Ленина, д. д. 24, кв./офис кв. 15', '2026-07-11 13:05:56', '2026-07-13 06:45:26'),
(14, 3, 'completed', 2798.00, 'г. Ухта, ул. ул. Ленина, д. д. 24, кв./офис кв. 15', '2026-07-11 13:07:40', '2026-07-13 06:45:26'),
(15, 3, 'completed', 1898.00, 'г. Ухта, ул. Lesnaya, д. 10, под. 2, кв./офис 45', '2026-07-11 14:48:30', '2026-07-13 06:45:26'),
(16, 2, 'completed', 4497.00, 'г. Ухта, ул. Куратова, д. 10, под. 1, кв./офис 45', '2026-07-12 14:21:47', NULL),
(17, 8, 'processing', 2998.00, 'г. Ухта, ул. Ленина, д. 1, под. 12, кв./офис 123', '2026-07-12 14:46:34', NULL),
(18, 8, 'pending', 4279.00, 'г. Ухта, ул. Ленина, д. 1, под. 12, кв./офис 123', '2026-07-12 15:30:05', NULL),
(19, 8, 'completed', 3296.00, 'г. Ухта, ул. Ленина, д. 1, под. 12, кв./офис 123', '2026-07-13 06:53:07', NULL),
(20, 9, 'pending', 1290.00, 'г. Ухта, ул. Lenina, д. 10, под. 1, кв./офис 25', '2026-07-13 07:02:14', NULL),
(21, 1, 'pending', 1290.00, 'г. Ухта, ул. Куратова, д. 10, под. 1, кв./офис 45', '2026-07-13 17:17:12', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `product_name`) VALUES
(1, 1, 1, 2, 1299.00, 'Стейк Рибай'),
(2, 1, 5, 1, 599.00, 'Бургер Огонёк'),
(3, 2, 17, 1, 799.00, 'Свиные ребра гриль'),
(4, 2, 11, 1, 599.00, 'Фирменный салат Огонёк'),
(5, 3, 3, 1, 1699.00, 'Стейк Филе-Миньон'),
(6, 3, 17, 1, 799.00, 'Свиные ребра гриль'),
(7, 4, 4, 1, 2499.00, 'Стейк Томагавк'),
(8, 4, 1, 1, 1299.00, 'Стейк Рибай'),
(9, 4, 17, 1, 799.00, 'Свиные ребра гриль'),
(10, 5, 7, 1, 649.00, 'BBQ Бекон Бургер'),
(11, 5, 18, 1, 499.00, 'Куриные крылышки BBQ &amp; Cheese'),
(12, 6, 1, 1, 1299.00, 'Стейк Рибай'),
(13, 6, 17, 1, 799.00, 'Свиные ребра гриль'),
(14, 6, 5, 1, 599.00, 'Бургер Огонёк'),
(15, 7, 1, 3, 1299.00, 'Стейк Рибай'),
(16, 8, 1, 1, 1299.00, 'Стейк Рибай'),
(17, 9, 5, 2, 599.00, 'Бургер Огонёк'),
(18, 9, 6, 1, 499.00, 'Классический Чизбургер'),
(19, 10, 1, 2, 1299.00, 'Стейк Рибай'),
(20, 10, 4, 1, 2499.00, 'Стейк Томагавк'),
(21, 10, 5, 1, 599.00, 'Бургер Огонёк'),
(22, 11, 20, 2, 1190.00, 'Лосось на углях'),
(23, 12, 1, 1, 1299.00, 'Стейк Рибай'),
(24, 13, 1, 1, 1299.00, 'Стейк Рибай'),
(25, 14, 1, 1, 1299.00, 'Стейк Рибай'),
(26, 14, 2, 1, 1499.00, 'Стейк Нью-Йорк'),
(27, 15, 1, 1, 1299.00, 'Стейк Рибай'),
(28, 15, 5, 1, 599.00, 'Бургер Огонёк'),
(29, 16, 1, 1, 1299.00, 'Стейк Рибай'),
(30, 16, 2, 1, 1499.00, 'Стейк Нью-Йорк'),
(31, 16, 3, 1, 1699.00, 'Стейк Филе-Миньон'),
(32, 17, 2, 2, 1499.00, 'Стейк Нью-Йорк'),
(33, 18, 2, 2, 1490.00, 'Стейк Нью-Йорк'),
(34, 18, 1, 1, 1299.00, 'Стейк Рибай'),
(35, 19, 1, 1, 1299.00, 'Стейк Рибай'),
(36, 19, 5, 1, 599.00, 'Бургер Огонёк'),
(37, 19, 11, 1, 599.00, 'Фирменный салат Огонёк'),
(38, 19, 15, 1, 300.00, 'Апельсиновый фреш'),
(39, 19, 18, 1, 499.00, 'Куриные крылышки BBQ &amp; Cheese'),
(40, 20, 1, 1, 1290.00, 'Стейк Рибай'),
(41, 21, 1, 1, 1290.00, 'Стейк Рибай');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT 'images/steak-bg.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `image_path`, `created_at`, `deleted_at`) VALUES
(1, 1, 'Стейк Рибай', 'Мраморная говядина зернового откорма. Сочный и насыщенный вкус.', 1290.00, 'images/steak-bg.png', '2026-06-07 14:01:04', NULL),
(2, 1, 'Стейк Нью-Йорк', 'Стейк из тонкого края мраморной говядины с выраженным мясным вкусом.', 1490.00, 'images/steak-bg.png', '2026-06-07 14:01:04', NULL),
(3, 1, 'Стейк Филе-Миньон', 'Самое нежное и постное мясо из центральной части вырезки.', 1699.00, 'images/steak-bg.png', '2026-06-07 14:01:04', NULL),
(4, 1, 'Стейк Томагавк', 'Стейк Рибай на кости с сочным вкусом и эффектной подачей.', 2499.00, 'images/steak-bg.png', '2026-06-07 14:01:04', NULL),
(5, 2, 'Бургер Огонёк', 'Сочная котлета на гриле, сыр чеддер, хрустящий бекон, фирменный соус.', 599.00, 'images/burger-item.png', '2026-06-07 14:01:04', NULL),
(6, 2, 'Классический Чизбургер', 'Котлета из мраморной говядины, двойной сыр, маринованные огурцы, горчичный соус.', 499.00, 'images/burger-item.png', '2026-06-07 14:01:04', NULL),
(7, 2, 'BBQ Бекон Бургер', 'Котлета из говядины, карамелизированный лук, бекон, соус BBQ.', 649.00, 'images/burger-item.png', '2026-06-07 14:01:04', NULL),
(8, 2, 'Острый Халапеньо Бургер', 'Острая котлета с перчиками халапеньо, томатами и пикантным соусом.', 579.00, 'images/burger-item.png', '2026-06-07 14:01:04', NULL),
(9, 3, 'Салат Цезарь Гриль', 'Сочное куриное филе на гриле, листья салата романо, томаты черри, пармезан, фирменный соус.', 499.00, 'images/salad-item.png', '2026-06-07 14:01:04', NULL),
(10, 3, 'Тёплый салат с говядиной', 'Мраморная говядина, микс салатов, сладкий перец, томаты черри, медово-горчичная заправка.', 649.00, 'images/salad-item.png', '2026-06-07 14:01:04', NULL),
(11, 3, 'Фирменный салат Огонёк', 'Ростбиф, печёный болгарский перец, руккола, вяленые томаты, пармезан, фирменный соус.', 599.00, 'images/salad-item.png', '2026-06-07 14:01:04', NULL),
(12, 3, 'Салат с тигровыми креветками', 'Обжаренные тигровые креветки, микс салатов, авокадо, огурец, лимонная заправка.', 699.00, 'images/salad-item.png', '2026-06-07 14:01:04', NULL),
(13, 4, 'Домашний лимонад', 'Освежающий лимонад собственного приготовления на основе цитрусов.', 250.00, 'images/drink-item.png', '2026-06-07 14:01:04', NULL),
(14, 4, 'Ягодный морс', 'Натуральный прохладительный напиток из лесных ягод.', 180.00, 'images/drink-item.png', '2026-06-07 14:01:04', NULL),
(15, 4, 'Апельсиновый фреш', 'Свежевыжатый сок из отборных апельсинов.', 300.00, 'images/drink-item.png', '2026-06-07 14:01:04', NULL),
(16, 4, 'Чай Таёжный сбор', 'Ароматный травяной чай с добавлением ягод и хвои.', 220.00, 'images/drink-item.png', '2026-06-07 14:01:04', NULL),
(17, 5, 'Свиные ребра гриль', 'Нежные свиные ребрышки в глазури барбекю с картофельными дольками.', 799.00, 'images/hot-item.png', '2026-06-07 14:01:04', NULL),
(18, 5, 'Куриные крылышки BBQ', 'Острые хрустящие крылышки, обжаренные на гриле.', 499.00, 'images/hot-item.png', '2026-06-07 14:01:04', NULL),
(19, 5, 'Гриль-микс из колбасок', 'Ассорти из домашних колбасок гриль с тушеной капустой и горчицей.', 890.00, 'images/hot-item.png', '2026-06-07 14:01:04', NULL),
(20, 5, 'Лосось на углях', 'Филе лосося на гриле со сливочно-икорным соусом и лимоном.', 1190.00, 'images/hot-item.png', '2026-06-07 14:01:04', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Администратор ресторана', 'admin@ogonyok.ru', '+7 (999) 111-22-33', '$2y$10$oZsqS8JmdkYOrDJkFVQf0.Gy4ttbks9GSlqEB7j1B3zT2teM.nTFq', 'admin', '2026-06-07 14:01:04'),
(2, 'Александр', 'user123@ogonyok.ru', '+7 (111) 111-11-11', '$2y$10$n.tF8DoFJoeu1jE4f2MW/.rvEt.fwZloZHszpQzylvUpYXI/heFXe', 'user', '2026-06-07 14:01:04'),
(3, 'Дмитрий Петров', 'dmitry@ogonyek.ru', '+7 (999) 555-44-33', '$2y$10$n.tF8DoFJoeu1jE4f2MW/.rvEt.fwZloZHszpQzylvUpYXI/heFXe', 'user', '2026-06-07 17:55:14'),
(4, 'Мария Сидорова', 'maria@ogonyek.ru', '+7 (999) 777-88-99', '$2y$10$n.tF8DoFJoeu1jE4f2MW/.rvEt.fwZloZHszpQzylvUpYXI/heFXe', 'user', '2026-06-07 17:55:14'),
(5, 'Иван Смирнов', 'ivan@ogonyek.ru', '+7 (999) 222-33-44', '$2y$10$n.tF8DoFJoeu1jE4f2MW/.rvEt.fwZloZHszpQzylvUpYXI/heFXe', 'user', '2026-06-07 17:55:14'),
(6, 'Елена Кузнецова', 'elena@ogonyek.ru', '+7 (999) 111-55-66', '$2y$10$n.tF8DoFJoeu1jE4f2MW/.rvEt.fwZloZHszpQzylvUpYXI/heFXe', 'user', '2026-06-07 17:55:14'),
(7, 'Тест Пользователь', 'testuser@example.com', '+7 (900) 123-45-67', '$2y$10$7ZOP5/YNQm2kc9oUsmNcMeMwEYgS87VsdvyA8fIzGCYwgaceEe9s2', 'user', '2026-07-11 14:18:10'),
(8, 'Коля', 'kolya@user.com', '+7 (111) 111-11-11', '$2y$10$yZqDnA26i8g9nI6ywaChuO.gzpcQRz6ABnjgmEvND4cWxBH/yDsi6', 'user', '2026-07-11 14:18:52'),
(9, 'User', 'user@ogonyok.ru', '+7 (999) 999-99-99', '$2y$10$D0ee/RJbAcj.bdtys0qLTu.V8/dJemt1SWPC25rqNL3vDRgT5GZam', 'user', '2026-07-13 07:00:57');

-- --------------------------------------------------------

--
-- Структура таблицы `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `city` varchar(50) DEFAULT 'Ухта',
  `street` varchar(100) NOT NULL,
  `house` varchar(20) NOT NULL,
  `entrance` varchar(20) DEFAULT NULL,
  `apartment` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `city`, `street`, `house`, `entrance`, `apartment`, `created_at`) VALUES
(1, 3, 'Дом', 'Ухта', 'ул. Ленина', 'д. 24', NULL, 'кв. 15', '2026-06-07 17:55:14'),
(2, 4, 'Дом', 'Ухта', 'ул. Куратова', 'д. 6', NULL, 'кв. 89', '2026-06-07 17:55:14'),
(3, 4, 'Работа', 'Ухта', 'ул. Мира', 'д. 12', '2', 'офис 4', '2026-06-07 17:55:14'),
(4, 6, 'Дом', 'Ухта', 'ул. Сенюкова', 'д. 8', NULL, 'кв. 112', '2026-06-07 17:55:14'),
(5, 2, 'Дом', 'Ухта', 'Куратова', '10', '1', '45', '2026-07-11 13:02:37'),
(6, 8, 'Дом', 'Ухта', 'Ленина', '1', '12', '123', '2026-07-12 14:46:34'),
(7, 9, '', 'Ухта', 'Lenina', '10', '1', '25', '2026-07-13 07:02:14'),
(8, 1, 'Дом', 'Ухта', 'Куратова', '10', '1', '45', '2026-07-13 17:16:41');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_bookings_date` (`booking_date`);

--
-- Индексы таблицы `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Индексы таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_addresses_user` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
