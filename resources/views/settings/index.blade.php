@extends('layouts.app', [
    'title' => 'Studio & Invoice Settings',
    'breadcrumbs' => ['Home' => '/', 'Settings' => route('settings.index')]
])

@section('content')
@php
    $tabs = [
        'general' => [
            'label' => 'General / Branding',
            'icon' => 'palette',
            'subtitle' => 'Studio name, logo & visual identity',
        ],
        'contact' => [
            'label' => 'Contact & Billing Information',
            'icon' => 'map-pin',
            'subtitle' => 'Physical studio location, address, phone & email',
        ],
        'taxation' => [
            'label' => 'GST & Taxation',
            'icon' => 'receipt',
            'subtitle' => 'GSTIN, default tax rate & SAC service codes',
        ],
        'banking' => [
            'label' => 'Bank & UPI Settlement',
            'icon' => 'landmark',
            'subtitle' => 'Direct transfer bank account & UPI payment QR code',
        ],
        'terms' => [
            'label' => 'Terms & Signatory',
            'icon' => 'pen-tool',
            'subtitle' => 'Invoice payment terms, thank-you notes & authorized signature',
        ],
    ];
@endphp

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                    {{ $isTelecallerScope ? "Invoice Settings — {$targetUser?->name}" : 'Studio & Invoice Settings' }}
                </h2>
                @if($isTelecallerScope)
                    <x-badge variant="primary" size="xs">Telecaller Profile</x-badge>
                @else
                    <x-badge variant="neutral" size="xs">Studio Master</x-badge>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $isTelecallerScope ? "Invoices generated for {$targetUser?->name}'s leads will use these personal invoice prefix, bank account, UPI ID, and signature details" : 'Configure studio branding, official GST registration, bank settlement details, and invoice formatting' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()?->isAdmin())
                <x-button variant="primary" size="sm" icon="camera" href="{{ route('shoot-types.index') }}">
                    <span>Manage Shoot Types</span>
                </x-button>
            @endif
            <x-button variant="secondary" size="sm" icon="file-text" href="{{ route('invoices.index') }}">
                <span>View Invoices</span>
            </x-button>
        </div>
    </div>

    @if(auth()->user()?->isAdmin() && $telecallers->isNotEmpty())
        <!-- Admin Profile Switcher -->
        <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-[#4F46E5] flex items-center justify-center shrink-0">
                    <x-icon name="user-check" class="w-4 h-4" />
                </div>
                <div>
                    <span class="font-bold text-slate-900 block">Configuring Invoice Setting Profile</span>
                    <span class="text-[11px] text-slate-500">Each telecaller can have their own custom invoice prefix, bank details, and UPI ID</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label for="telecaller-selector" class="text-xs font-semibold text-slate-600 shrink-0">Switch Profile:</label>
                <select 
                    id="telecaller-selector"
                    onchange="window.location.href = this.value" 
                    class="text-xs rounded-lg border-slate-200 bg-white font-medium text-slate-800 py-1.5 pl-3 pr-8 focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="{{ route('settings.index') }}" @selected(!$targetUser)>
                        Studio Master Default
                    </option>
                    @foreach($telecallers as $tc)
                        <option value="{{ route('settings.index', ['telecaller_id' => $tc->id]) }}" @selected($targetUser?->id === $tc->id)>
                            {{ $tc->name }} ({{ ucfirst($tc->role) }}) — Prefix: {{ $tc->studioSetting?->invoice_prefix ?? strtoupper(substr($tc->name, 0, 4)) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <p class="font-bold flex items-center gap-1.5">
                <x-icon name="alert-circle" class="w-4 h-4 text-rose-600 shrink-0" />
                <span>Please correct the errors below:</span>
            </p>
            <ul class="list-disc list-inside pl-1 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Interactive Tab Navigation Bar -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-1.5 shadow-2xs">
        <nav class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0" id="settings-tab-list" aria-label="Settings Tabs">
            @foreach($tabs as $key => $tab)
                <button 
                    type="button" 
                    id="tab-btn-{{ $key }}"
                    onclick="switchSettingsTab('{{ $key }}')"
                    class="tab-btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-medium whitespace-nowrap transition-all duration-150 cursor-pointer {{ $loop->first ? 'active bg-indigo-50 text-indigo-700 font-bold border border-indigo-200/80 shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-transparent' }}"
                >
                    <x-icon :name="$tab['icon']" class="w-4 h-4 shrink-0 transition-colors" />
                    <span>{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="settings-form">
        @csrf
        @method('PUT')
        @if($targetUser)
            <input type="hidden" name="telecaller_id" value="{{ $targetUser->id }}">
        @endif

        <!-- TAB 1: GENERAL & BRANDING -->
        <div id="tab-pane-general" class="tab-pane space-y-6">
            <x-card title="General / Studio Branding" subtitle="Visual identity, logo, and studio brand credentials">
                <div class="space-y-6">
                    <!-- Logo Upload Section -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Studio Logo for Invoices & Portals</label>
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                                <!-- Fixed Dimensions Logo Preview Container -->
                                <div class="relative rounded-xl bg-white border border-slate-200/90 shadow-2xs overflow-hidden shrink-0"
                                     style="width: 140px; height: 72px; min-width: 140px; max-width: 140px; min-height: 72px; max-height: 72px; display: flex; align-items: center; justify-content: center; padding: 6px; box-sizing: border-box; flex-shrink: 0; background: #ffffff;">
                                    <div id="logo-preview-fallback" 
                                         class="{{ $settings->logo_url ? 'hidden' : '' }}" 
                                         style="width: 100%; height: 100%; display: {{ $settings->logo_url ? 'none' : 'flex' }}; flex-direction: column; align-items: center; justify-content: center; background-color: #eef2ff; border-radius: 0.5rem; color: #4f46e5;">
                                        <x-icon name="aperture" class="w-6 h-6" />
                                        <span style="font-size: 9px; font-weight: 700; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em;">Default Logo</span>
                                    </div>
                                    <img id="logo-preview" 
                                         src="{{ $settings->logo_url ?: '#' }}" 
                                         alt="{{ $settings->studio_name }}" 
                                         class="{{ $settings->logo_url ? '' : 'hidden' }}"
                                         style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: {{ $settings->logo_url ? 'block' : 'none' }}; margin: auto;">
                                </div>

                                <!-- Upload & Removal Controls -->
                                <div class="space-y-2 flex-1">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <label for="logo-file-input" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs cursor-pointer transition-colors">
                                            <x-icon name="upload" class="w-3.5 h-3.5 text-indigo-600" />
                                            <span>Upload New Logo</span>
                                        </label>
                                        <input 
                                            id="logo-file-input" 
                                            type="file" 
                                            name="logo" 
                                            accept="image/png,image/jpeg,image/webp,image/svg+xml" 
                                            class="hidden"
                                            onchange="previewUploadedLogo(this)"
                                        />

                                        @if($settings->logo_path)
                                            <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 cursor-pointer">
                                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                                <span>Reset to default logo</span>
                                            </label>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">
                                        Recommended format: Transparent PNG or SVG. Scaled cleanly up to 48px height on formal Tax Invoices.
                                    </p>
                                </div>
                            </div>

                            <!-- Live Invoice Header Mockup Preview -->
                            <div class="pt-3 border-t border-slate-200/70">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <x-icon name="file-text" class="w-3.5 h-3.5 text-indigo-500" />
                                        <span>Invoice Header Live Preview</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">Simulated Print Output (48px max height)</span>
                                </div>
                                <div class="p-3.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <div style="height: 44px; max-height: 44px; display: flex; align-items: center; margin-bottom: 6px;">
                                                <img id="invoice-header-mockup-logo" 
                                                     src="{{ $settings->logo_url ?: '#' }}" 
                                                     alt="{{ $settings->studio_name }}" 
                                                     class="{{ $settings->logo_url ? '' : 'hidden' }}" 
                                                     style="max-height: 44px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: {{ $settings->logo_url ? 'block' : 'none' }};">
                                                <span id="invoice-header-mockup-text" class="{{ $settings->logo_url ? 'hidden' : '' }} text-lg font-black text-slate-900 tracking-tight" style="display: {{ $settings->logo_url ? 'none' : 'inline' }};">
                                                    {{ $settings->studio_name }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 leading-snug space-y-0.5">
                                                <p class="font-medium text-slate-700 truncate">{{ $settings->full_address }}</p>
                                                <p>GSTIN: <span class="font-mono text-slate-900 font-semibold">{{ $settings->gstin }}</span> &bull; SAC: <span class="font-mono text-slate-700">{{ $settings->hsn_sac_code }}</span></p>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-wider bg-slate-900 text-white uppercase">Tax Invoice</span>
                                            <p class="text-[11px] font-mono text-slate-500 mt-1">{{ $settings->invoice_prefix }}-2026-001</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Core Brand Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input 
                                label="Studio Name" 
                                name="studio_name" 
                                :value="$settings->studio_name" 
                                placeholder="e.g. ShutterCode Studio" 
                                required 
                            />
                        </div>

                        <div>
                            <x-input 
                                label="Studio Tagline / Slogan" 
                                name="studio_tagline" 
                                :value="$settings->studio_tagline" 
                                placeholder="e.g. Fine Art Photography & Cinematography" 
                            />
                        </div>

                        <div>
                            <x-input 
                                label="Invoice Number Prefix" 
                                name="invoice_prefix" 
                                :value="$settings->invoice_prefix" 
                                placeholder="SHSN" 
                                required 
                            />
                            <span class="text-[10px] text-slate-400 mt-1 block">Default prefix for generating invoice numbers (e.g. SHSN-2026-001)</span>
                        </div>
                    </div>
                </div>

                <!-- Tab Action Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" onclick="switchSettingsTab('contact')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-colors">
                        <span>Next: Contact & Billing Information</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </x-card>
        </div>

        <!-- TAB 2: CONTACT & BILLING INFORMATION -->
        <div id="tab-pane-contact" class="tab-pane space-y-6 hidden">
            <x-card title="Contact & Billing Information" subtitle="Physical studio address, official billing email, and client contact phones">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input 
                                label="Primary Phone / WhatsApp" 
                                name="phone" 
                                :value="$settings->phone" 
                                placeholder="e.g. +91 98450 00001" 
                                required 
                            />
                            <span class="text-[10px] text-slate-400 mt-1 block">Printed on invoice header for client inquiries</span>
                        </div>

                        <div>
                            <x-input 
                                label="Official Billing Email" 
                                name="email" 
                                type="email" 
                                :value="$settings->email" 
                                placeholder="e.g. billing@shuttercode.com" 
                                required 
                            />
                            <span class="text-[10px] text-slate-400 mt-1 block">Official email printed under studio details</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-3">Registered Studio Postal Address</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-3">
                                <x-input 
                                    label="Street Address / Suite / Landmark" 
                                    name="address" 
                                    :value="$settings->address" 
                                    placeholder="e.g. 84, 100 Feet Road, Indiranagar" 
                                    required 
                                />
                            </div>

                            <div>
                                <x-input 
                                    label="City" 
                                    name="city" 
                                    :value="$settings->city" 
                                    placeholder="e.g. Bengaluru" 
                                    required 
                                />
                            </div>

                            <div>
                                <x-input 
                                    label="State" 
                                    name="state" 
                                    :value="$settings->state" 
                                    placeholder="e.g. Karnataka" 
                                    required 
                                />
                            </div>

                            <div>
                                <x-input 
                                    label="Pincode / Postal Code" 
                                    name="pincode" 
                                    :value="$settings->pincode" 
                                    placeholder="e.g. 560038" 
                                    required 
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Action Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="switchSettingsTab('general')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-xs font-semibold transition-colors">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                        <span>Previous: General</span>
                    </button>
                    <button type="button" onclick="switchSettingsTab('taxation')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-colors">
                        <span>Next: GST & Taxation</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </x-card>
        </div>

        <!-- TAB 3: GST & TAXATION -->
        <div id="tab-pane-taxation" class="tab-pane space-y-6 hidden">
            <x-card title="GST & Taxation Configuration" subtitle="Statutory GSTIN identification, default tax rates, and SAC service codes">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input 
                            label="GSTIN (Goods and Services Tax Identification Number)" 
                            name="gstin" 
                            :value="$settings->gstin" 
                            placeholder="e.g. 29AAAAA0000A1Z5" 
                        />
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: 2 digits State Code (e.g. 29 for KA) + 10 digits PAN + 3 entity chars</span>
                    </div>

                    <div>
                        <x-select label="Default GST Tax Rate (%)" name="default_tax_rate" required>
                            <option value="18.00" @selected(old('default_tax_rate', $settings->default_tax_rate) == '18.00')>18% GST (9% CGST + 9% SGST)</option>
                            <option value="12.00" @selected(old('default_tax_rate', $settings->default_tax_rate) == '12.00')>12% GST (6% CGST + 6% SGST)</option>
                            <option value="5.00" @selected(old('default_tax_rate', $settings->default_tax_rate) == '5.00')>5% GST (2.5% CGST + 2.5% SGST)</option>
                            <option value="0.00" @selected(old('default_tax_rate', $settings->default_tax_rate) == '0.00')>0% (Exempt / Nil Rated)</option>
                        </x-select>
                        <span class="text-[10px] text-slate-400 mt-1 block">Pre-selected when creating new invoices</span>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input 
                            label="SAC / HSN Service Code" 
                            name="hsn_sac_code" 
                            :value="$settings->hsn_sac_code" 
                            placeholder="9983" 
                        />
                        <span class="text-[10px] text-slate-400 mt-1 block">Standard SAC code <strong>9983</strong> for Photographic and Video Recording Services</span>
                    </div>
                </div>

                <!-- Tab Action Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="switchSettingsTab('contact')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-xs font-semibold transition-colors">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                        <span>Previous: Contact & Billing</span>
                    </button>
                    <button type="button" onclick="switchSettingsTab('banking')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-colors">
                        <span>Next: Bank & UPI Settlement</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </x-card>
        </div>

        <!-- TAB 4: BANK & UPI SETTLEMENT -->
        <div id="tab-pane-banking" class="tab-pane space-y-6 hidden">
            <x-card title="Bank & UPI Settlement Details" subtitle="Bank account specifications and instant scan UPI QR code printed on invoices">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input 
                            label="Bank Name" 
                            name="bank_name" 
                            :value="$settings->bank_name" 
                            placeholder="e.g. HDFC Bank Ltd" 
                            required 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Beneficiary / Account Holder Name" 
                            name="bank_account_name" 
                            :value="$settings->bank_account_name" 
                            placeholder="e.g. ShutterCode Studio Pvt Ltd" 
                            required 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Account Number" 
                            name="bank_account_number" 
                            :value="$settings->bank_account_number" 
                            placeholder="e.g. 50200012345678" 
                            required 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="IFSC Code" 
                            name="bank_ifsc" 
                            :value="$settings->bank_ifsc" 
                            placeholder="e.g. HDFC0001234" 
                            required 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Bank Branch" 
                            name="bank_branch" 
                            :value="$settings->bank_branch" 
                            placeholder="e.g. Indiranagar 100 Feet Road, Bengaluru" 
                        />
                    </div>

                    <div>
                        <x-input 
                            label="Studio UPI ID (VPA)" 
                            name="upi_id" 
                            :value="$settings->upi_id" 
                            placeholder="e.g. shuttercode@hdfcbank" 
                            required 
                        />
                        <span class="text-[10px] text-slate-400 mt-1 block">Used to automatically generate the dynamic UPI QR code on client invoices</span>
                    </div>
                </div>

                <!-- Tab Action Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="switchSettingsTab('taxation')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-xs font-semibold transition-colors">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                        <span>Previous: GST & Taxation</span>
                    </button>
                    <button type="button" onclick="switchSettingsTab('terms')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-colors">
                        <span>Next: Terms & Signatory</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </x-card>
        </div>

        <!-- TAB 5: TERMS & SIGNATORY -->
        <div id="tab-pane-terms" class="tab-pane space-y-6 hidden">
            <x-card title="Invoice Terms & Authorized Signatory" subtitle="Standard contractual payment terms, invoice notes, and official digital seal details">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input 
                                label="Authorized Signatory Full Name" 
                                name="authorized_signatory_name" 
                                :value="$settings->authorized_signatory_name" 
                                placeholder="e.g. Rahul Rao" 
                            />
                        </div>

                        <div>
                            <x-input 
                                label="Signatory Title / Designation" 
                                name="authorized_signatory_title" 
                                :value="$settings->authorized_signatory_title" 
                                placeholder="e.g. Studio Director & Lead Artist" 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Default Payment Terms & Conditions</label>
                        <textarea 
                            name="invoice_terms" 
                            rows="4" 
                            class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15 leading-relaxed"
                        >{{ old('invoice_terms', $settings->invoice_terms) }}</textarea>
                        <span class="text-[10px] text-slate-400 mt-1 block">Printed on the bottom-left of every generated customer invoice</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Footer Note / Thank-You Message</label>
                        <input 
                            type="text" 
                            name="invoice_notes" 
                            value="{{ old('invoice_notes', $settings->invoice_notes) }}" 
                            class="w-full text-xs rounded-lg border border-slate-300 p-2.5 placeholder-slate-400 focus:outline-hidden focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/15"
                            placeholder="e.g. Thank you for choosing ShutterCode Studio!"
                        />
                    </div>
                </div>

                <!-- Tab Action Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="switchSettingsTab('banking')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-xs font-semibold transition-colors">
                        <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                        <span>Previous: Bank & UPI</span>
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#4F46E5] hover:bg-[#4338CA] text-white text-xs font-semibold transition-colors shadow-xs">
                        <x-icon name="check" class="w-3.5 h-3.5" />
                        <span>Save Studio Settings</span>
                    </button>
                </div>
            </x-card>
        </div>

        <!-- Sticky Save Floating Bar (Always Visible Across All Tabs) -->
        <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-lg flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <x-icon name="info" class="w-4 h-4 text-slate-400 hidden sm:inline shrink-0" />
                <span>All settings across all tabs will be updated together when you save.</span>
            </div>
            <div class="flex items-center gap-2.5 ml-auto">
                <x-button variant="secondary" size="md" href="{{ route('dashboard') }}">
                    Discard
                </x-button>
                <x-button variant="primary" size="md" type="submit" icon="check">
                    Save Studio Settings
                </x-button>
            </div>
        </div>
    </form>
</div>

<script>
    function switchSettingsTab(tabKey) {
        // 1. Hide all tab panes
        const panes = document.querySelectorAll('.tab-pane');
        panes.forEach(pane => pane.classList.add('hidden'));

        // 2. Reset active styling on all tab buttons
        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => {
            btn.classList.remove('active', 'bg-indigo-50', 'text-indigo-700', 'font-bold', 'border-indigo-200/80', 'shadow-2xs');
            btn.classList.add('text-slate-600', 'border-transparent');
        });

        // 3. Reveal targeted pane
        const targetPane = document.getElementById('tab-pane-' + tabKey);
        if (targetPane) {
            targetPane.classList.remove('hidden');
        }

        // 4. Activate targeted button
        const targetBtn = document.getElementById('tab-btn-' + tabKey);
        if (targetBtn) {
            targetBtn.classList.remove('text-slate-600', 'border-transparent');
            targetBtn.classList.add('active', 'bg-indigo-50', 'text-indigo-700', 'font-bold', 'border-indigo-200/80', 'shadow-2xs');
        }

        // 5. Update browser URL hash cleanly
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, null, '#' + tabKey);
        } else {
            window.location.hash = '#' + tabKey;
        }

        // Re-render lucide icons if needed
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function previewUploadedLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                const fallback = document.getElementById('logo-preview-fallback');
                const mockupLogo = document.getElementById('invoice-header-mockup-logo');
                const mockupText = document.getElementById('invoice-header-mockup-text');

                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    preview.style.display = 'block';
                    preview.style.maxWidth = '100%';
                    preview.style.maxHeight = '100%';
                    preview.style.width = 'auto';
                    preview.style.height = 'auto';
                    preview.style.objectFit = 'contain';
                }
                if (fallback) {
                    fallback.classList.add('hidden');
                    fallback.style.display = 'none';
                }
                if (mockupLogo) {
                    mockupLogo.src = e.target.result;
                    mockupLogo.classList.remove('hidden');
                    mockupLogo.style.display = 'block';
                    mockupLogo.style.maxHeight = '44px';
                    mockupLogo.style.maxWidth = '220px';
                    mockupLogo.style.width = 'auto';
                    mockupLogo.style.height = 'auto';
                    mockupLogo.style.objectFit = 'contain';
                }
                if (mockupText) {
                    mockupText.classList.add('hidden');
                    mockupText.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Auto-activate tab based on validation errors or URL hash on load
    document.addEventListener('DOMContentLoaded', function() {
        // If there are validation errors, jump to the tab containing the error
        const errorInput = document.querySelector('.tab-pane input.border-rose-300, .tab-pane textarea.border-rose-300, .tab-pane .text-rose-600');
        if (errorInput) {
            const errorPane = errorInput.closest('.tab-pane');
            if (errorPane && errorPane.id) {
                const tabKey = errorPane.id.replace('tab-pane-', '');
                switchSettingsTab(tabKey);
                return;
            }
        }

        // Otherwise check hash
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            if (document.getElementById('tab-pane-' + hash)) {
                switchSettingsTab(hash);
            }
        }
    });
</script>
@endsection
