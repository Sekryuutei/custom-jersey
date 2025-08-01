                    <tr>
                        <td>{{ $template->id }}</td>
                        <td>
                            @php
                                $imageUrl = Illuminate\Support\Str::startsWith($template->image_path, 'http')
                                    ? $template->image_path
                                    : asset('assets/' . $template->image_path);
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $template->name }}" style="width: 100px; height: auto;">
                        </td>
                        <td>{{ $template->name }}</td>
                        <td>
