<?php
declare(strict_types=1);
namespace Kashida\BranchLocator;
if ( ! defined( 'ABSPATH' ) ) exit;
final class Plugin {
    private static ?self $instance = null;
    private ?\Kashida\BranchLocator\Shortcodes\Branch_Locator_Shortcode $shortcode = null;
    private ?\Kashida\BranchLocator\Admin\Admin $admin = null;
    public static function instance(): self {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }
    private function __construct() {}
    public function init(): void {
        // Arabic fallback translations for the complete plugin UI. This supplements the PO/MO catalog so labels remain localized even when the catalog is incomplete.
        add_filter( 'gettext_kashida-branch-locator', [ $this, 'translate_arabic_ui' ], 20, 3 );
        add_filter( 'ngettext_kashida-branch-locator', [ $this, 'translate_arabic_ui_plural' ], 20, 5 );
        $this->shortcode = new \Kashida\BranchLocator\Shortcodes\Branch_Locator_Shortcode();
        if ( is_admin() ) {
            $this->admin = new \Kashida\BranchLocator\Admin\Admin();
        }
    }

    public function translate_arabic_ui( string $translation, string $text, string $domain ): string {
        if ( 0 !== strpos( determine_locale(), 'ar' ) ) {
            return $translation;
        }
        static $translations = [
            'Add New Group' => 'إضافة مجموعة جديدة',
            'Add New Branch' => 'إضافة فرع جديد',
            'Add Branch' => 'إضافة فرع',
            'Add Group' => 'إضافة مجموعة',
            'Branches' => 'الفروع',
            'Branch' => 'الفرع',
            'Groups' => 'المجموعات',
            'Delete' => 'حذف',
            'Edit' => 'تعديل',
            'Display' => 'عرض',
            'Actions' => 'الإجراءات',
            'Coordinates' => 'الإحداثيات',
            'Select all groups' => 'تحديد جميع المجموعات',
            'Select all on this page' => 'تحديد الكل في هذه الصفحة',
            'Delete Selected' => 'حذف المحدد',
            'Create Display Code' => 'إنشاء رمز العرض',
            'Create display code' => 'إنشاء رمز العرض',
            'Open Groups' => 'فتح المجموعات',
            'Open Import' => 'فتح الاستيراد',
            'Import' => 'استيراد',
            'Help' => 'المساعدة',
            'Help page' => 'صفحة المساعدة',
            'Kashida Branch Locator' => 'كشيدة لإدارة الفروع',
            'Manage locations, groups, coordinates and display codes.' => 'إدارة المواقع والمجموعات والإحداثيات ورموز العرض.',
            'A simple way to manage branches, groups, maps, and responsive location directories.' => 'طريقة سهلة لإدارة الفروع والمجموعات والخرائط وأدلة المواقع المتجاوبة.',
            'Add your data' => 'أضف بياناتك',
            'Build the display' => 'أنشئ طريقة العرض',
            'Paste and publish' => 'انسخ وانشر',
            'Open Display from a group, choose the layout and fields, then generate the shortcode.' => 'افتح العرض من إحدى المجموعات، واختر التخطيط والحقول، ثم أنشئ الرمز القصير.',
            'Create groups and branches manually, or import them from XLSX or CSV.' => 'أنشئ المجموعات والفروع يدويًا، أو استوردها من ملفات XLSX أو CSV.',
            'Paste the generated shortcode into any WordPress page, post, widget, or builder shortcode element.' => 'الصق الرمز القصير الناتج في أي صفحة أو مقالة أو ودجت أو عنصر رموز قصيرة في أداة بناء الصفحات.',
            'Display Builder' => 'منشئ العرض',
            'Display Layout' => 'تخطيط العرض',
            'Information to Show' => 'المعلومات المطلوب عرضها',
            'Options' => 'الخيارات',
            'Live Preview' => 'معاينة مباشرة',
            'Preview only' => 'معاينة فقط',
            'Preview only — your selected settings will be used in the generated shortcode.' => 'هذه معاينة فقط — ستُستخدم الإعدادات المحددة عند إنشاء الرمز القصير.',
            'Generated Shortcode' => 'الرمز القصير الناتج',
            'Copy Shortcode' => 'نسخ الرمز القصير',
            'Copy shortcode' => 'نسخ الرمز القصير',
            'Copy' => 'نسخ',
            'Copied' => 'تم النسخ',
            'Cancel' => 'إلغاء',
            'Save' => 'حفظ',
            'Close' => 'إغلاق',
            'Search' => 'بحث',
            'Filter' => 'تصفية',
            'Filter or search' => 'تصفية أو بحث',
            'Clear search' => 'مسح البحث',
            'Reset' => 'إعادة ضبط',
            'Branch Name *' => 'اسم الفرع *',
            'Group Name *' => 'اسم المجموعة *',
            'Name' => 'الاسم',
            'Name is required.' => 'الاسم مطلوب.',
            'Branch name' => 'اسم الفرع',
            'Branch name, city, address or phone number' => 'اسم الفرع أو المدينة أو العنوان أو رقم الهاتف',
            'City' => 'المدينة',
            'Address' => 'العنوان',
            'Phone' => 'رقم الهاتف',
            'Website' => 'الموقع الإلكتروني',
            'Group' => 'المجموعة',
            'All Cities' => 'كل المدن',
            'All Groups' => 'كل المجموعات',
            'All branches' => 'كل الفروع',
            'City filter' => 'تصفية حسب المدينة',
            'Group filter' => 'تصفية حسب المجموعة',
            'Pagination' => 'تقسيم الصفحات',
            'Sorting' => 'الترتيب',
            'Call button' => 'زر الاتصال',
            'Directions button' => 'زر الاتجاهات',
            'Call button color' => 'لون زر الاتصال',
            'Directions button color' => 'لون زر الاتجاهات',
            'Website button color' => 'لون زر الموقع الإلكتروني',
            'Cards columns' => 'عدد أعمدة البطاقات',
            'Group cards under group headings' => 'تجميع البطاقات تحت عناوين المجموعات',
            'Results per page' => 'عدد النتائج في الصفحة',
            'Map height' => 'ارتفاع الخريطة',
            'Map zoom' => 'مستوى تكبير الخريطة',
            'Table' => 'جدول',
            'Cards' => 'بطاقات',
            'List' => 'قائمة',
            'Accordion' => 'قائمة قابلة للطي',
            'Map' => 'خريطة',
            'Map + Table' => 'خريطة وجدول',
            'Map + table' => 'خريطة وجدول',
            'Layouts' => 'التخطيطات',
            'Choose layout' => 'اختر التخطيط',
            'Choose layout and fields' => 'اختر التخطيط والحقول',
            'Choose fields' => 'اختر الحقول',
            'Choose the right presentation' => 'اختر طريقة العرض المناسبة',
            'Modern responsive cards with optional actions.' => 'بطاقات متجاوبة وعصرية مع إجراءات اختيارية.',
            'A clean tabular view for structured branch data.' => 'عرض جدولي منظم لبيانات الفروع.',
            'A compact list for simple directories.' => 'قائمة مختصرة لأدلة الفروع.',
            'Expandable branch details that save space.' => 'تفاصيل فروع قابلة للطي لتوفير المساحة.',
            'Interactive map with clickable branch markers.' => 'خريطة تفاعلية بعلامات قابلة للنقر للفروع.',
            'Interactive map linked to the branch table.' => 'خريطة تفاعلية مرتبطة بجدول الفروع.',
            'Branch created.' => 'تم إنشاء الفرع.',
            'Branch updated.' => 'تم تحديث الفرع.',
            'Branch deleted.' => 'تم حذف الفرع.',
            'Group created.' => 'تم إنشاء المجموعة.',
            'Group updated.' => 'تم تحديث المجموعة.',
            'Group deleted.' => 'تم حذف المجموعة.',
            'No branches found.' => 'لم يتم العثور على فروع.',
            'No groups yet.' => 'لا توجد مجموعات حتى الآن.',
            'Showing %1$d of %2$d results.' => 'عرض %1$d من أصل %2$d نتيجة.',
            'Ascending' => 'تصاعدي',
            'Descending' => 'تنازلي',
            'Click to sort' => 'انقر للترتيب',
            'Delete this branch? This action cannot be undone.' => 'هل تريد حذف هذا الفرع؟ لا يمكن التراجع عن هذا الإجراء.',
            'Delete this group? Its branches will be unassigned. This action cannot be undone.' => 'هل تريد حذف هذه المجموعة؟ ستصبح فروعها غير مرتبطة بمجموعة. لا يمكن التراجع عن هذا الإجراء.',
            'Delete %d selected branches? This action cannot be undone.' => 'هل تريد حذف %d من الفروع المحددة؟ لا يمكن التراجع عن هذا الإجراء.',
            'Delete %d selected groups? Their branches will be unassigned. This action cannot be undone.' => 'هل تريد حذف %d من المجموعات المحددة؟ ستصبح فروعها غير مرتبطة بمجموعة. لا يمكن التراجع عن هذا الإجراء.',
            'Are you sure you want to delete this?' => 'هل أنت متأكد من رغبتك في الحذف؟',
            'Are you sure you want to delete all selected items?' => 'هل أنت متأكد من رغبتك في حذف جميع العناصر المحددة؟',
            'Bulk tools' => 'أدوات الإجراءات الجماعية',
            'Import Branches' => 'استيراد الفروع',
            'Import Complete' => 'اكتمل الاستيراد',
            'Import Failed' => 'فشل الاستيراد',
            'Importing…' => 'جارٍ الاستيراد…',
            'Preview Import' => 'معاينة الاستيراد',
            'Download Import Template' => 'تنزيل قالب الاستيراد',
            'Download sample file' => 'تنزيل ملف نموذجي',
            'Download Error Report' => 'تنزيل تقرير الأخطاء',
            'Browse File' => 'استعراض الملفات',
            'Drag & drop your file here, or click to browse' => 'اسحب الملف وأفلته هنا، أو انقر للاستعراض',
            'Please select a file.' => 'يرجى اختيار ملف.',
            'Only CSV and Excel files are supported.' => 'تُدعم ملفات CSV وExcel فقط.',
            'Accepted formats: .xlsx, .csv (legacy .xls requires PhpSpreadsheet)' => 'الصيغ المقبولة: \u200e.xlsx و\u200e.csv (تتطلب ملفات \u200e.xls القديمة مكتبة PhpSpreadsheet)',
            'Prepare your file' => 'جهّز ملفك',
            'Preview' => 'معاينة',
            'Run Import' => 'بدء الاستيراد',
            'Ready' => 'جاهز',
            'Error' => 'خطأ',
            'Errors:' => 'الأخطاء:',
            'Status' => 'الحالة',
            'Latitude' => 'خط العرض',
            'Longitude' => 'خط الطول',
            'Invalid coordinates.' => 'الإحداثيات غير صالحة.',
            'Invalid group.' => 'المجموعة غير صالحة.',
            'Invalid ID.' => 'المعرّف غير صالح.',
            'Invalid upload.' => 'الملف المرفوع غير صالح.',
            'Permission denied.' => 'تم رفض الإذن.',
            'Request failed.' => 'فشل الطلب.',
            'Loading…' => 'جارٍ التحميل…',
            'No valid coordinates are available for these branches.' => 'لا تتوفر إحداثيات صالحة لهذه الفروع.',
            'Map preview' => 'معاينة الخريطة',
            'Common issues' => 'المشكلات الشائعة',
            'Recommended workflow' => 'سير العمل الموصى به',
            'Ready-to-use examples' => 'أمثلة جاهزة للاستخدام',
            'Performance' => 'الأداء',
            'Multiple branches' => 'عدة فروع',
            'Multiple groups' => 'عدة مجموعات',
            'How do I combine branches or groups?' => 'كيف أجمع الفروع أو المجموعات؟',
            'How do I sort a table?' => 'كيف أرتب الجدول؟',
            'How does the table search work?' => 'كيف يعمل البحث في الجدول؟',
            'Do map layouts require an API key?' => 'هل تتطلب عروض الخرائط مفتاح API؟',
            'Inspect the location' => 'التحقق من الموقع',
            'Mobile map controls' => 'عناصر التحكم بالخريطة على الهاتف',
            'Map interaction' => 'التفاعل مع الخريطة',
            'No shortcode knowledge required' => 'لا تحتاج إلى معرفة مسبقة بالرموز القصيرة',
            'New in v2.0.4' => 'جديد في الإصدار 2.0.4',
            'Call' => 'اتصال',
            'Directions' => 'الاتجاهات',
        ];
        return $translations[ $text ] ?? $translation;
    }

    public function translate_arabic_ui_plural( string $translation, string $single, string $plural, int $number, string $domain ): string {
        if ( 0 !== strpos( determine_locale(), 'ar' ) ) {
            return $translation;
        }
        $key = ( 1 === $number ) ? $single : $plural;
        return $this->translate_arabic_ui( $translation, $key, $domain );
    }
}
