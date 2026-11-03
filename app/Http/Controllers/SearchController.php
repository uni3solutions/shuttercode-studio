<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Perform global studio spotlight search across shoots, clients, leads, and staff.
     */
    public function global(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'total' => 0,
                'results' => [
                    'shoots' => [],
                    'customers' => [],
                    'leads' => [],
                    'team' => [],
                ],
            ]);
        }

        $user = auth()->user();

        // 1. Shoots
        $shootsQuery = Shoot::with('customer')
            ->where(function ($q) use ($query) {
                $q->where('shoot_number', 'like', "%{$query}%")
                    ->orWhere('title', 'like', "%{$query}%")
                    ->orWhere('event_type', 'like', "%{$query}%")
                    ->orWhere('venue', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($cq) use ($query) {
                        $cq->where('name', 'like', "%{$query}%");
                    });
            });

        if ($user?->isTelecaller()) {
            $shootsQuery->where('created_by', $user->id);
        }

        $shoots = $shootsQuery->latest('shoot_date')
            ->take(5)
            ->get()
            ->map(function ($shoot) {
                return [
                    'id' => $shoot->id,
                    'title' => $shoot->title ?: "{$shoot->shoot_number} ({$shoot->event_type})",
                    'subtitle' => ($shoot->customer?->name ?? 'No Client').' • '.($shoot->shoot_date?->format('d M Y') ?? 'Date TBD').($shoot->venue ? " • {$shoot->venue}" : ''),
                    'badge' => ucfirst($shoot->status),
                    'badge_color' => $shoot->status === 'completed' ? 'emerald' : ($shoot->status === 'in_progress' ? 'indigo' : 'amber'),
                    'url' => route('shoots.show', $shoot),
                    'icon' => 'camera',
                    'category' => 'Shoots & Bookings',
                ];
            });

        // 2. Customers
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'title' => $customer->name,
                    'subtitle' => $customer->phone.($customer->email ? " • {$customer->email}" : '').($customer->city ? " • {$customer->city}" : ''),
                    'badge' => 'Customer',
                    'badge_color' => 'blue',
                    'url' => route('customers.show', $customer),
                    'icon' => 'users',
                    'category' => 'Customers',
                ];
            });

        // 3. Leads
        $leadsQuery = Lead::where(function ($q) use ($query) {
            $q->where('customer_name', 'like', "%{$query}%")
                ->orWhere('lead_number', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('event_type', 'like', "%{$query}%");
        });

        if ($user?->isTelecaller()) {
            $leadsQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        $leads = $leadsQuery->take(5)
            ->get()
            ->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'title' => $lead->customer_name ?: $lead->lead_number,
                    'subtitle' => "{$lead->lead_number} • {$lead->event_type} • {$lead->phone}",
                    'badge' => ucfirst($lead->status),
                    'badge_color' => $lead->status === 'new' ? 'emerald' : 'slate',
                    'url' => route('leads.show', $lead),
                    'icon' => 'message-square-quote',
                    'category' => 'Leads & Enquiries',
                ];
            });

        // 4. Team (Photographers & Staff)
        $team = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('specialization', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($user) {
                $isPhotographer = $user->role === 'photographer';

                return [
                    'id' => $user->id,
                    'title' => $user->name,
                    'subtitle' => ($user->specialization ?: $user->phone).' • '.ucfirst(str_replace('_', ' ', $user->role)),
                    'badge' => $isPhotographer ? 'Photographer' : 'Crew / Staff',
                    'badge_color' => $isPhotographer ? 'indigo' : 'purple',
                    'url' => $isPhotographer ? route('photographers.index') : route('crew.index'),
                    'icon' => $isPhotographer ? 'camera' : 'user-check',
                    'category' => 'Studio Team',
                ];
            });

        $total = $shoots->count() + $customers->count() + $leads->count() + $team->count();

        return response()->json([
            'query' => $query,
            'total' => $total,
            'results' => [
                'shoots' => $shoots,
                'customers' => $customers,
                'leads' => $leads,
                'team' => $team,
            ],
        ]);
    }
}
