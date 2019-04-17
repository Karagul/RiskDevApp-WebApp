
CREATE TABLE `animal_master` (
  `animal_code` char(5) primary key COLLATE utf8mb4_unicode_ci NOT NULL,
  `animal_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO animal_master VALUES
('10101', 'กระบือ'),
('10202', 'โคนม'),
('10207', 'โคเนื้อ'),
('10301', 'ม้า'),
('20101', 'สุกร'),
('20201', 'แพะ'),
('20301', 'แกะ'),
('30101', 'ไก่'),
('30103', 'ไก่ไข่'),
('30108', 'ไก่เนื้อ'),
('30110', 'ไก่พื้นเมือง'),
('30301', 'เป็ด'),
('30801', 'นกกระทา'),
('60101', 'สุนัข'),
('60201', 'แมว');