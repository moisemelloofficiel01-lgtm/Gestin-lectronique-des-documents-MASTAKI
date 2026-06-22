class User {
  final int id;
  final String? matricule;
  final String? nom;
  final String? prenom;
  final String username;
  final String email;
  final String? fonction;
  final String? photo;
  final List<String> roles;

  User({
    required this.id,
    this.matricule,
    this.nom,
    this.prenom,
    required this.username,
    required this.email,
    this.fonction,
    this.photo,
    required this.roles,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      matricule: json['matricule'] as String?,
      nom: json['nom'] as String?,
      prenom: json['prenom'] as String?,
      username: json['username'] as String? ?? '',
      email: json['email'] as String? ?? '',
      fonction: json['fonction'] as String?,
      photo: json['photo'] as String?,
      roles: (json['roles'] as List?)?.map((e) => e.toString()).toList() ?? [],
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'matricule': matricule,
        'nom': nom,
        'prenom': prenom,
        'username': username,
        'email': email,
        'fonction': fonction,
        'photo': photo,
        'roles': roles,
      };
}
