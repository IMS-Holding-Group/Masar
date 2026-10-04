-- تشغيل مرة واحدة على قواعد موجودة قبل إضافة الحقل في masar.sql
-- المرحلة العلمية: بكالوريوس / دبلوم

ALTER TABLE STUDENT
    ADD COLUMN education_level VARCHAR(50) NOT NULL DEFAULT 'بكالوريوس' AFTER academic_year;
