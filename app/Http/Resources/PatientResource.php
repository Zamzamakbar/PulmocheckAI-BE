<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'result' => $this->result,
            'image' => $this->image ? asset('images/' . $this->image) : null, 
            'cnn_accuracy' => $this->cnn_accuracy,
            'cnn_auc' => $this->cnn_auc,
            'cnn_label' => $this->cnn_label,
            'vit_accuracy' => $this->vit_accuracy,
            'vit_auc' => $this->vit_auc,
            'vit_label' => $this->vit_label,
            'validation_doctor' => $this->validation_doctor,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'date_formatted' => $this->created_at->isoFormat('dddd, D MMMM Y'),
        ];
    }
}

