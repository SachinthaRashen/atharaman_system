<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleRequest extends Model
{
    protected $fillable = [
        'user_id', 
        'request_type', 
        'business_name', 
        'contact_number', 
        'credentials_description', 
        'document_url', 
        'status', 
        'admin_notes'
    ];

    // Links back to the user applying for the role
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}