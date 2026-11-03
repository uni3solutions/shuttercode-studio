<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PhotographerPayment;
use App\Models\Shoot;
use App\Models\ShootAssignment;
use App\Models\ShootDelivery;
use App\Models\ShootType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core Users & Team Members
        $superAdmin = User::create([
            'name' => 'Rahul Rao',
            'email' => 'superadmin@shuttercode.com',
            'phone' => '+91 98450 00001',
            'role' => 'super_admin',
            'specialization' => 'Studio Director & Lead Artist',
            'password' => Hash::make('password'),
        ]);

        $admin = User::create([
            'name' => 'Vikram Malhotra',
            'email' => 'admin@shuttercode.com',
            'phone' => '+91 98450 00002',
            'role' => 'admin',
            'specialization' => 'Studio Operations Manager',
            'password' => Hash::make('password'),
        ]);

        $telecallerNeha = User::create([
            'name' => 'Neha Sharma',
            'email' => 'neha@shuttercode.com',
            'phone' => '+91 98450 00003',
            'role' => 'telecaller',
            'specialization' => 'Senior Client Relationship Lead',
            'password' => Hash::make('password'),
        ]);

        $telecallerKiran = User::create([
            'name' => 'Kiran Gowda',
            'email' => 'kiran@shuttercode.com',
            'phone' => '+91 98450 00004',
            'role' => 'telecaller',
            'specialization' => 'WhatsApp Lead Specialist',
            'password' => Hash::make('password'),
        ]);

        $photographerArun = User::create([
            'name' => 'Arun Kumar',
            'email' => 'arun@shuttercode.com',
            'phone' => '+91 98450 00005',
            'role' => 'photographer',
            'specialization' => 'Lead Candid & Wedding Specialist',
            'password' => Hash::make('password'),
        ]);

        $photographerKavya = User::create([
            'name' => 'Kavya Murthy',
            'email' => 'kavya@shuttercode.com',
            'phone' => '+91 98450 00006',
            'role' => 'photographer',
            'specialization' => 'Cinematographer & Drone Pilot',
            'password' => Hash::make('password'),
        ]);

        $photographerDeepak = User::create([
            'name' => 'Deepak Rao',
            'email' => 'deepak@shuttercode.com',
            'phone' => '+91 98450 00007',
            'role' => 'photographer',
            'specialization' => 'Traditional & Portrait Specialist',
            'password' => Hash::make('password'),
        ]);

        $photographerNikhil = User::create([
            'name' => 'Nikhil Varma',
            'email' => 'nikhil@shuttercode.com',
            'phone' => '+91 98450 00008',
            'role' => 'photographer',
            'specialization' => 'Senior Colorist & Editor',
            'password' => Hash::make('password'),
        ]);

        $customerUser = User::create([
            'name' => 'Rohit Sharma',
            'email' => 'rohit@example.com',
            'phone' => '+91 98450 12345',
            'role' => 'customer',
            'password' => Hash::make('password'),
        ]);

        // 1b. Seed Default Shoot Types
        $initialTypes = [
            ['name' => 'Wedding', 'description' => 'Traditional, royal, and destination wedding events'],
            ['name' => 'Pre-Wedding', 'description' => 'Couples portrait shoots, cinematic teasers, and outdoor sessions'],
            ['name' => 'Maternity', 'description' => 'Maternity, pregnancy glow, and baby bump portraits'],
            ['name' => 'Baby Shoot', 'description' => 'Newborn, infant milestones, cake smash, and toddler portraits'],
            ['name' => 'Corporate', 'description' => 'Corporate summits, headshots, conferences, and office events'],
            ['name' => 'Birthday', 'description' => 'Birthday parties, anniversaries, and family celebrations'],
            ['name' => 'Fashion', 'description' => 'Editorial, portfolio, runway, and commercial model shoots'],
            ['name' => 'Other', 'description' => 'Custom and specialized photography requirements'],
        ];

        foreach ($initialTypes as $type) {
            ShootType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'slug' => Str::slug($type['name']),
                    'description' => $type['description'],
                    'is_active' => true,
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        // 2. Create Photography Packages
        $pkgRoyalWedding = Package::create([
            'name' => 'Royal Heritage Grand Wedding (3 Days)',
            'category' => 'Wedding',
            'price' => 150000.00,
            'advance_amount' => 50000.00,
            'duration_hours' => 24,
            'photographers_count' => 2,
            'videographers_count' => 2,
            'drone_included' => true,
            'album_count' => 2,
            'deliverables' => "• 2 Candid Photographers + 1 Traditional\n• 1 Cinematic Video Director + 1 Traditional Videographer\n• 4K Drone Aerial Coverage\n• 2 Premium Leatherette Flush-Mount Albums (80 Pages each)\n• Cinematic Wedding Teaser (3-4 mins) + Full Length Film (45 mins)\n• Online Private Gallery with 1-Year Cloud Storage",
            'description' => 'Our flagship ultra-luxury wedding coverage capturing every emotion across Sangeet, Wedding, and Grand Reception.',
            'created_by' => $telecallerNeha->id,
            'is_active' => true,
        ]);

        $pkgPreWedding = Package::create([
            'name' => 'Cinematic Pre-Wedding Destination',
            'category' => 'Pre-Wedding',
            'price' => 65000.00,
            'advance_amount' => 25000.00,
            'duration_hours' => 8,
            'photographers_count' => 1,
            'videographers_count' => 1,
            'drone_included' => true,
            'album_count' => 1,
            'deliverables' => "• 1 Candid Storyteller + 1 Cinematographer\n• Aerial Drone Drone Shots\n• 3 Costume/Location Changes\n• 1 Signature Coffee Table Photo Book\n• 3-Minute Cinematic Story Video with Licensed Soundtrack",
            'description' => 'Romantic destination shoot with cinematic visuals and artistic sunset portraits.',
            'created_by' => $telecallerNeha->id,
            'is_active' => true,
        ]);

        $pkgComboWedding = Package::create([
            'name' => 'Traditional + Candid Combo (1 Day)',
            'category' => 'Wedding',
            'price' => 85000.00,
            'advance_amount' => 30000.00,
            'duration_hours' => 10,
            'photographers_count' => 2,
            'videographers_count' => 1,
            'drone_included' => false,
            'album_count' => 1,
            'deliverables' => "• 1 Candid Photographer + 1 Traditional Photographer\n• 1 Full HD Video Coverage\n• 1 Premium Lay-Flat Wedding Album (60 Pages)\n• Edited Highlights Video\n• All High-Res Edited Digital Photos",
            'description' => 'Ideal for single-day wedding ceremonies combining contemporary candid moments and family stage portraits.',
            'created_by' => $telecallerKiran->id,
            'is_active' => true,
        ]);

        $pkgMaternity = Package::create([
            'name' => 'Maternity & New Life',
            'category' => 'Maternity',
            'price' => 35000.00,
            'advance_amount' => 10000.00,
            'duration_hours' => 4,
            'photographers_count' => 1,
            'videographers_count' => 0,
            'drone_included' => false,
            'album_count' => 1,
            'deliverables' => "• 1 Maternity Portrait Specialist\n• Studio A & Lush Outdoor Garden Setups\n• Maternity Gowns & Props Provided\n• 25 Retouched Fine-Art Digital Portraits\n• 1 Mini Keepsake Album",
            'description' => 'Gentle, glowing portraits honoring the mother-to-be and beginning of new life.',
            'created_by' => $telecallerKiran->id,
            'is_active' => true,
        ]);

        $pkgCorporate = Package::create([
            'name' => 'Corporate Leadership Summit & Expo',
            'category' => 'Corporate',
            'price' => 45000.00,
            'advance_amount' => 20000.00,
            'duration_hours' => 8,
            'photographers_count' => 1,
            'videographers_count' => 1,
            'drone_included' => false,
            'album_count' => 0,
            'deliverables' => "• Keynote Speaker & Audience Coverage\n• Executive Portrait Pop-up Booth\n• Same-Day Press Release 20 Edited Photos\n• 2-Minute Corporate Aftermovie",
            'description' => 'High-precision commercial photography and video for enterprise summits and conventions.',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $pkgBirthday = Package::create([
            'name' => 'Grand 1st Birthday & Cake Smash',
            'category' => 'Birthday',
            'price' => 25000.00,
            'advance_amount' => 8000.00,
            'duration_hours' => 4,
            'photographers_count' => 1,
            'videographers_count' => 0,
            'drone_included' => false,
            'album_count' => 1,
            'deliverables' => "• Themed Studio Decor / Cake Smash Setup\n• Family & Guest Portraits\n• 40 Edited Digital Photos\n• 1 Handcrafted Memory Book",
            'description' => 'Fun-filled joyful memories of your baby’s first milestone celebration.',
            'created_by' => $telecallerNeha->id,
            'is_active' => true,
        ]);

        // 3. Create Customers
        $c1 = Customer::create([
            'name' => 'Rohit & Sneha Sharma',
            'phone' => '+91 98450 12345',
            'email' => 'rohit@example.com',
            'alternate_phone' => '+91 98450 12346',
            'address' => 'Villa 42, Prestige Golfshire',
            'city' => 'Bengaluru',
            'notes' => 'VIP client. Prefers warm film tone and sunset candid portraits.',
            'user_id' => $customerUser->id,
        ]);

        $c2 = Customer::create([
            'name' => 'Aarav Gupta & Divya',
            'phone' => '+91 98765 43210',
            'email' => 'aarav.gupta@gmail.com',
            'alternate_phone' => '+91 98765 43211',
            'address' => '702, Sobha Iris, Outer Ring Road',
            'city' => 'Bengaluru',
            'notes' => 'Pre-wedding shoot scheduled at Nandi Hills Resort.',
        ]);

        $c3 = Customer::create([
            'name' => 'Meera Nair & Siddharth',
            'phone' => '+91 99001 88776',
            'email' => 'meera.nair@outlook.com',
            'address' => '12, 4th Main, Malleshwaram',
            'city' => 'Bengaluru',
            'notes' => 'Traditional Kerala wedding ceremony followed by modern reception.',
        ]);

        $c4 = Customer::create([
            'name' => 'Tech Corp India Pvt Ltd',
            'phone' => '+91 80234 56789',
            'email' => 'events@techcorp.in',
            'address' => 'Tower C, EGL Tech Park, Domlur',
            'city' => 'Bengaluru',
            'notes' => 'Annual Global Leadership Summit. Requires GST invoice.',
        ]);

        $c5 = Customer::create([
            'name' => 'Ananya Verma',
            'phone' => '+91 98860 11223',
            'email' => 'ananya.v@gmail.com',
            'address' => 'Flat 301, Brigade Millennium, JP Nagar',
            'city' => 'Bengaluru',
            'notes' => 'Maternity shoot in studio with pastel flower backdrop.',
        ]);

        $c6 = Customer::create([
            'name' => 'Rajesh & Sunita Kulkarni',
            'phone' => '+91 98451 44556',
            'email' => 'rajesh.kulkarni@yahoo.com',
            'address' => '55, 8th Cross, Jayanagar 4th Block',
            'city' => 'Bengaluru',
            'notes' => '25th Silver Wedding Anniversary celebration.',
        ]);

        // 4. Create Leads (WhatsApp & Web enquiries)
        $lead1 = Lead::create([
            'lead_number' => 'LD-2026-001',
            'customer_name' => 'Priya Sen',
            'phone' => '+91 91234 56789',
            'email' => 'priya.sen@gmail.com',
            'event_type' => 'Destination Wedding (Goa)',
            'event_date' => now()->addDays(45),
            'venue' => 'Taj Exotica, Benaulim, Goa',
            'estimated_budget' => 220000.00,
            'source' => 'WhatsApp Meta',
            'assigned_to' => $telecallerNeha->id,
            'created_by' => $telecallerNeha->id,
            'status' => 'in_discussion',
            'notes' => 'Inquired on WhatsApp about 3-day beach wedding package. Sent initial brochure.',
        ]);

        LeadActivity::create([
            'lead_id' => $lead1->id,
            'user_id' => $telecallerNeha->id,
            'type' => 'whatsapp',
            'details' => 'Received WhatsApp enquiry via Meta Ads. Shared Royal Heritage catalog.',
            'next_follow_up_at' => now()->addHours(2),
        ]);

        $lead2 = Lead::create([
            'lead_number' => 'LD-2026-002',
            'customer_name' => 'Varun Iyer',
            'phone' => '+91 94480 33445',
            'email' => 'varun.iyer@gmail.com',
            'event_type' => 'Haldi & Sangeet Ceremony',
            'event_date' => now()->addDays(20),
            'venue' => 'The Tamarind Tree, Kanakapura Road',
            'estimated_budget' => 90000.00,
            'source' => 'Website Form',
            'assigned_to' => $telecallerNeha->id,
            'created_by' => $telecallerNeha->id,
            'status' => 'contacted',
            'notes' => 'Requested customized 2-day quote with candid photography.',
        ]);

        $lead3 = Lead::create([
            'lead_number' => 'LD-2026-003',
            'customer_name' => 'Sunil Reddy',
            'phone' => '+91 98451 99887',
            'email' => 'sunil.reddy@gmail.com',
            'event_type' => 'Baby 1st Birthday Shoot',
            'event_date' => now()->addDays(12),
            'venue' => 'ShutterCode Studio A',
            'estimated_budget' => 30000.00,
            'source' => 'Instagram DM',
            'assigned_to' => $telecallerKiran->id,
            'created_by' => $telecallerKiran->id,
            'status' => 'converted',
            'notes' => 'Visited studio, loved the cake smash setup. Converted to booking!',
        ]);

        $lead4 = Lead::create([
            'lead_number' => 'LD-2026-004',
            'customer_name' => 'Kavitha Hegde',
            'phone' => '+91 99800 11224',
            'email' => 'kavitha.hegde@gmail.com',
            'event_type' => 'Pre-Wedding Shoot',
            'event_date' => now()->addDays(30),
            'venue' => 'Chikmagalur Coffee Estate',
            'estimated_budget' => 75000.00,
            'source' => 'WhatsApp Meta',
            'assigned_to' => $telecallerKiran->id,
            'created_by' => $telecallerKiran->id,
            'status' => 'interested',
            'notes' => 'Sent customized quotation for 2 days outstation shoot including drone. Client very interested.',
        ]);

        // 5. Create Shoots & Financial Transactions
        // Shoot 1: Active In-Progress Wedding Shoot
        $shoot1 = Shoot::create([
            'shoot_number' => 'SHT-2026-001',
            'title' => 'Rohit & Sneha — Royal Wedding & Reception',
            'customer_id' => $c1->id,
            'package_id' => $pkgRoyalWedding->id,
            'event_type' => 'Wedding',
            'shoot_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '22:00:00',
            'venue' => 'The Leela Palace, Old Airport Road, Bengaluru',
            'location_url' => 'https://maps.google.com/?q=The+Leela+Palace+Bengaluru',
            'requirements' => 'Cover bride getting ready at 10 AM, groom baraat at 11:30 AM, muhurtham at 12:45 PM, stage reception 7 PM.',
            'status' => 'in_progress',
            'total_amount' => 150000.00,
            'discount_amount' => 0.00,
            'advance_amount' => 50000.00,
            'paid_amount' => 100000.00,
            'balance_amount' => 50000.00,
            'created_by' => $admin->id,
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot1->id,
            'user_id' => $photographerArun->id,
            'role' => 'lead_photographer',
            'payout_agreed' => 18000.00,
            'status' => 'confirmed',
            'notes' => 'Primary candid shooter with Sony A7 IV + 24-70 GM.',
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot1->id,
            'user_id' => $photographerKavya->id,
            'role' => 'drone_pilot',
            'payout_agreed' => 16000.00,
            'status' => 'confirmed',
            'notes' => 'DJI Mavic 3 Pro aerial drone coverage.',
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot1->id,
            'user_id' => $photographerDeepak->id,
            'role' => 'traditional_photographer',
            'payout_agreed' => 12000.00,
            'status' => 'confirmed',
            'notes' => 'Stage portraits and family group photos.',
        ]);

        PhotographerPayment::create([
            'payment_number' => 'CPAY-2026-001',
            'shoot_id' => $shoot1->id,
            'user_id' => $photographerArun->id,
            'amount' => 10000.00,
            'payment_method' => 'upi',
            'status' => 'paid',
            'paid_at' => now()->subDay(),
            'transaction_reference' => 'UPI/482910293847',
            'notes' => 'Milestone advance payment for Wedding shoot.',
        ]);

        Payment::create([
            'payment_number' => 'PAY-2026-001',
            'shoot_id' => $shoot1->id,
            'customer_id' => $c1->id,
            'amount' => 50000.00,
            'payment_method' => 'upi',
            'payment_type' => 'advance',
            'transaction_reference' => 'UPI/428901928374',
            'status' => 'verified',
            'paid_at' => now()->subDays(15),
            'notes' => 'Booking advance payment.',
            'recorded_by' => $admin->id,
        ]);

        Payment::create([
            'payment_number' => 'PAY-2026-002',
            'shoot_id' => $shoot1->id,
            'customer_id' => $c1->id,
            'amount' => 50000.00,
            'payment_method' => 'bank_transfer',
            'payment_type' => 'part_payment',
            'transaction_reference' => 'NEFT/HDFC002819',
            'status' => 'verified',
            'paid_at' => now()->subDays(2),
            'notes' => 'Second milestone payment prior to shoot day.',
            'recorded_by' => $admin->id,
        ]);

        Invoice::updateOrCreate(
            ['shoot_id' => $shoot1->id],
            [
                'invoice_number' => 'SHSN-2026-001',
                'customer_id' => $c1->id,
                'subtotal' => 150000.00,
                'tax_rate' => 18.00,
                'tax_amount' => 27000.00,
                'total_amount' => 177000.00,
                'paid_amount' => 100000.00,
                'balance_amount' => 77000.00,
                'status' => 'partially_paid',
                'issue_date' => now()->subDays(15)->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'terms' => 'Final balance due on or before RAW gallery delivery.',
            ]
        );

        ShootDelivery::create([
            'shoot_id' => $shoot1->id,
            'raw_photos_count' => 1240,
            'raw_drive_link' => 'https://drive.google.com/drive/folders/shuttercode-sht-001-raw',
            'edited_photos_count' => 0,
            'client_gallery_link' => 'https://gallery.shuttercode.com/view/rohit-sneha-2026',
            'gallery_pin' => '4829',
            'delivery_status' => 'raw_uploaded',
            'submitted_by' => $photographerArun->id,
            'notes' => 'Card 1 & 2 ingested. Backup verified on Studio NAS.',
        ]);

        // Shoot 2: Confirmed Pre-Wedding Shoot Today Afternoon
        $shoot2 = Shoot::create([
            'shoot_number' => 'SHT-2026-002',
            'title' => 'Aarav Gupta & Divya — Cinematic Pre-Wedding',
            'customer_id' => $c2->id,
            'package_id' => $pkgPreWedding->id,
            'event_type' => 'Pre-Wedding',
            'shoot_date' => now()->toDateString(),
            'start_time' => '15:30:00',
            'end_time' => '19:30:00',
            'venue' => 'Discovery Village, Nandi Hills Foot, Bengaluru',
            'location_url' => 'https://maps.google.com/?q=Nandi+Hills+Bengaluru',
            'requirements' => 'Golden hour portraits + drone fly-by sequences in ethnic attire.',
            'status' => 'crew_assigned',
            'total_amount' => 65000.00,
            'discount_amount' => 0.00,
            'advance_amount' => 65000.00,
            'paid_amount' => 65000.00,
            'balance_amount' => 0.00,
            'created_by' => $telecallerNeha->id,
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot2->id,
            'user_id' => $photographerKavya->id,
            'role' => 'cinematographer',
            'payout_agreed' => 14000.00,
            'status' => 'confirmed',
        ]);

        Payment::create([
            'payment_number' => 'PAY-2026-003',
            'shoot_id' => $shoot2->id,
            'customer_id' => $c2->id,
            'amount' => 65000.00,
            'payment_method' => 'upi',
            'payment_type' => 'final_payment',
            'transaction_reference' => 'UPI/661902837491',
            'status' => 'verified',
            'paid_at' => now()->subDays(5),
            'notes' => 'Full package payment cleared.',
            'recorded_by' => $admin->id,
        ]);

        // Shoot 3: Upcoming Wedding Tomorrow
        $shoot3 = Shoot::create([
            'shoot_number' => 'SHT-2026-003',
            'title' => 'Meera & Siddharth — Sangeet & Reception',
            'customer_id' => $c3->id,
            'package_id' => $pkgComboWedding->id,
            'event_type' => 'Wedding',
            'shoot_date' => now()->addDay()->toDateString(),
            'start_time' => '18:00:00',
            'end_time' => '23:30:00',
            'venue' => 'The Taj West End, Race Course Road, Bengaluru',
            'status' => 'scheduled',
            'total_amount' => 85000.00,
            'discount_amount' => 5000.00,
            'advance_amount' => 30000.00,
            'paid_amount' => 30000.00,
            'balance_amount' => 50000.00,
            'created_by' => $telecallerKiran->id,
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot3->id,
            'user_id' => $photographerArun->id,
            'role' => 'lead_photographer',
            'payout_agreed' => 15000.00,
            'status' => 'assigned',
        ]);

        Payment::create([
            'payment_number' => 'PAY-2026-004',
            'shoot_id' => $shoot3->id,
            'customer_id' => $c3->id,
            'amount' => 30000.00,
            'payment_method' => 'card',
            'payment_type' => 'advance',
            'transaction_reference' => 'POS/8827361928',
            'status' => 'verified',
            'paid_at' => now()->subDays(10),
            'notes' => 'Advance token payment.',
            'recorded_by' => $admin->id,
        ]);

        // Shoot 4: Corporate Event in Editing
        $shoot4 = Shoot::create([
            'shoot_number' => 'SHT-2026-004',
            'title' => 'Tech Corp Global Leadership Summit',
            'customer_id' => $c4->id,
            'package_id' => $pkgCorporate->id,
            'event_type' => 'Corporate',
            'shoot_date' => now()->subDays(2)->toDateString(),
            'venue' => 'ITC Gardenia, Residency Road, Bengaluru',
            'status' => 'editing',
            'total_amount' => 45000.00,
            'paid_amount' => 45000.00,
            'balance_amount' => 0.00,
            'created_by' => $admin->id,
        ]);

        ShootDelivery::create([
            'shoot_id' => $shoot4->id,
            'raw_photos_count' => 650,
            'edited_photos_count' => 85,
            'client_gallery_link' => 'https://gallery.shuttercode.com/view/techcorp-summit',
            'gallery_pin' => '9901',
            'delivery_status' => 'editing',
            'submitted_by' => $photographerNikhil->id,
            'notes' => 'Highlights delivered for LinkedIn press release. Video edit in progress.',
        ]);

        // Shoot 5: Maternity Shoot Completed
        $shoot5 = Shoot::create([
            'shoot_number' => 'SHT-2026-005',
            'title' => 'Ananya Baby Shower & Maternity Glow',
            'customer_id' => $c5->id,
            'package_id' => $pkgMaternity->id,
            'event_type' => 'Maternity',
            'shoot_date' => now()->subDays(7)->toDateString(),
            'venue' => 'ShutterCode Studio A, Bengaluru',
            'status' => 'completed',
            'total_amount' => 35000.00,
            'paid_amount' => 35000.00,
            'balance_amount' => 0.00,
            'created_by' => $telecallerKiran->id,
        ]);

        ShootDelivery::create([
            'shoot_id' => $shoot5->id,
            'raw_photos_count' => 420,
            'edited_photos_count' => 35,
            'client_gallery_link' => 'https://gallery.shuttercode.com/view/ananya-maternity',
            'album_preview_link' => 'https://proofing.shuttercode.com/ananya-book',
            'gallery_pin' => '3312',
            'delivery_status' => 'delivered',
            'delivered_at' => now()->subDays(1),
            'submitted_by' => $photographerDeepak->id,
            'notes' => 'Customer approved album layout. Sent for printing.',
        ]);

        ShootAssignment::create([
            'shoot_id' => $shoot5->id,
            'user_id' => $photographerArun->id,
            'role' => 'lead_photographer',
            'payout_agreed' => 10000.00,
            'status' => 'completed',
            'notes' => 'Studio portrait specialist.',
        ]);

        PhotographerPayment::create([
            'payment_number' => 'CPAY-2026-002',
            'shoot_id' => $shoot5->id,
            'user_id' => $photographerArun->id,
            'amount' => 10000.00,
            'payment_method' => 'bank_transfer',
            'status' => 'paid',
            'paid_at' => now()->subDays(6),
            'transaction_reference' => 'NEFT/HDFC998218',
            'notes' => 'Full payout settled for completed maternity shoot.',
        ]);

        // 6. Log Initial Activity
        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'action' => 'system_bootstrapped',
            'description' => 'ShutterCode Studio initialized with packages, team roster, and live operational database.',
            'created_at' => now(),
        ]);
    }
}
