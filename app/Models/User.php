<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'department',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'created_by');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'created_by');
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'created_by');
    }

    public function postedJournals()
    {
        return $this->hasMany(Journal::class, 'posted_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }
}